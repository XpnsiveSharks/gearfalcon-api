<?php

namespace App\Presentation\Controllers;

// --- v0.0.0 PayMongo SDK ---
use Paymongo\PaymongoClient; // This is the correct client for v0.0.0
use Paymongo\Entities\Webhook; // This is the class to verify webhooks in this SDK version
use Paymongo\Exceptions\InvalidRequestException as PayMongoErrorException;
use Paymongo\Exceptions\SignatureVerificationException; // 👈 This is the same

// --- Standard PHP Exceptions ---
use \UnexpectedValueException;
use \Exception;

// Import your application's services and repositories
use App\Application\Customer\Services\CartService;
use App\Application\Customer\Services\JobService;
use App\Infrastructure\Repositories\CustomerAddressRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use App\Infrastructure\Models\User;

class PaymentController
{
    private CartService $cartService;
    private JobService $jobService;
    private CustomerAddressRepository $customerAddressRepo;
    private ServiceRepository $serviceRepository;

    public function __construct(
        CartService $cartService,
        JobService $jobService,
        CustomerAddressRepository $customerAddressRepo,
        ServiceRepository $serviceRepository
    ) {
        $this->cartService = $cartService;
        $this->jobService = $jobService;
        $this->customerAddressRepo = $customerAddressRepo;
        $this->serviceRepository = $serviceRepository;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function createPaymentSource(array $request): string
    {
        try {
            /** @var User|null $user */
            $user = $request['user'] ?? null;
            if (!$user || !$user->customer) {
                return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
            }
            $customerId = $user->customer->id;

            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
            $addressId = $data['customer_address_id'] ?? null;
            $scheduledDate = $data['scheduled_date'] ?? null;

            if (!$addressId) {
                return $this->jsonResponse(['error' => 'Customer address ID is required'], 400);
            }

            $address = $this->customerAddressRepo->findById($addressId);
            if (!$address || $address->customer_id !== $customerId) {
                return $this->jsonResponse(['error' => 'Invalid address selected'], 403);
            }

            $cart = $this->cartService->getCart($customerId);

            if (empty($cart['items'])) {
                return $this->jsonResponse(['error' => 'Your cart is empty'], 400);
            }

            $paymongoSecretKey = $_ENV['PAYMONGO_SECRET_KEY'];
            if (!$paymongoSecretKey) {
                error_log("PAYMONGO_SECRET_KEY is not set.");
                return $this->jsonResponse(['error' => 'Payment gateway is not configured.'], 500);
            }

            $client = new PaymongoClient($paymongoSecretKey);

            $totalAmount = (float) $cart['total_price'];

            if ($totalAmount < 100) {
                return $this->jsonResponse([
                    'error' => 'Minimum transaction amount is P100.00',
                    'cart_total' => $totalAmount,
                ], 400);
            }

            // Prepare metadata and sort it alphabetically by key.
            // This is CRITICAL to ensure the data structure matches what PayMongo uses
            // to generate the webhook signature, preventing "Invalid signature" errors.
            $metadata = [
                'cart_id' => (string) $cart['id'],
                'customer_id' => (string) $customerId,
                'customer_address_id' => (string) $addressId
            ];

            if ($scheduledDate) {
                $metadata['scheduled_date'] = $scheduledDate;
            }

            ksort($metadata);

            $source = $client->sources->create([
                'type' => 'gcash',
                'amount' => (int) ($totalAmount * 100),
                'currency' => 'PHP',
                'redirect' => [
                    'success' => $_ENV['APP_URL'] . '/customer/payment-success',
                    'failed' => $_ENV['APP_URL'] . '/customer/payment-cancelled',
                ],
                'billing' => [
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => [
                        'line1'       => $address->house_number . ' ' . $address->street,
                        'city'        => $address->city,
                        'state'       => $address->province,
                        'postal_code' => $address->postal_code,
                        'country'     => 'PH'
                    ]
                ],
                'metadata' => $metadata
            ]);

            // Link the PayMongo source ID to our cart for webhook processing
            $this->cartService->changeStatus((int) $cart['id'], 'active', ['payment_source_id' => $source->id]);

            return $this->jsonResponse([
                'redirectUrl' => $source->redirect['checkout_url']
            ]);
        } catch (PayMongoErrorException $e) {
            $errorDetails = [];
            if (is_array($e->getError())) {
                foreach ($e->getError() as $error) {
                    if (is_object($error) && isset($error->detail)) {
                        $errorDetails[] = $error->detail . ' (code: ' . ($error->code ?? 'N/A') . ')';
                    }
                }
            }
            $errorMessage = 'Payment gateway error: ' . ($errorDetails ? implode(', ', $errorDetails) : $e->getMessage());
            error_log($errorMessage);

            return $this->jsonResponse(['error' => $errorMessage], 500);
        } catch (Exception $e) {
            error_log("Create Payment Source Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
            return $this->jsonResponse(['error' => 'An internal error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function handleWebhook(array $request): string
    {
        $webhookSecret = $_ENV['PAYMONGO_WEBHOOK_SECRET'];
        if (!$webhookSecret) {
            error_log("CRITICAL: PAYMONGO_WEBHOOK_SECRET is not set.");
            return $this->jsonResponse(['error' => 'Webhook service not configured'], 500);
        }

        // Get the exact raw body from the request
        $payload = $request['raw_body'] ?? '';
        $signatureHeader = $request['server']['HTTP_PAYMONGO_SIGNATURE'] ?? null;

        if (!$signatureHeader) {
            error_log("Missing Paymongo-Signature header");
            return $this->jsonResponse(['error' => 'Missing Paymongo-Signature header'], 400);
        }

        // Debug logging
        error_log("=== PAYMONGO WEBHOOK RECEIVED ===");
        error_log("Payload length: " . strlen($payload));
        error_log("Signature header present: YES");
        error_log("First 100 chars: " . substr($payload, 0, 100));
        error_log("================================");

        try {
            // Use PayMongo SDK's built-in webhook verification
            $client = new \Paymongo\PaymongoClient($_ENV['PAYMONGO_SECRET_KEY']);

            // The constructEvent method handles all signature verification
            $event = $client->webhooks->constructEvent([
                'payload' => $payload,
                'signature_header' => $signatureHeader,
                'webhook_secret_key' => $webhookSecret
            ]);

            error_log("✓ Webhook signature verified. Event: {$event->id}, Type: {$event->type}");

            // Process the event based on type
            $eventType = is_object($event) ? $event->type : ($event['type'] ?? null);

            if ($eventType === 'source.chargeable') {
                // --- Direct Payload Parsing ---
                // The SDK's $event->resource is consistently null. We will parse the raw payload ourselves
                // to get the source data, as the payload structure is reliable.
                $payloadData = json_decode($payload, false); // Decode as object
                $sourceResource = $payloadData->data->attributes->data ?? null;

                if (!$sourceResource || !isset($sourceResource->id) || $sourceResource->type !== 'source') {
                    // Add a dump of the event object to see why resource is null
                    error_log("⚠ ERROR: Could not extract source resource from event. SDK Event Object: " . print_r($event, true));
                    return $this->jsonResponse(['status' => 'received']);
                }

                $sourceId = $sourceResource->id;

                if (!$sourceId) {
                    error_log("⚠ ERROR: Could not extract source ID from event");
                    return $this->jsonResponse(['status' => 'received']);
                }


                error_log("Processing source.chargeable for source: {$sourceId}");

                // Find the cart using the source ID
                $cartModel = $this->cartService->getCartBySourceId($sourceId);

                if ($cartModel) {
                    try {
                        // Create the payment using the chargeable source
                        $amount = $sourceResource->attributes->amount ?? 0;

                        $payment = $client->payments->create([
                            'amount' => $amount,
                            'source' => ['id' => $sourceId, 'type' => 'source'],
                            'currency' => 'PHP',
                            'description' => 'GearFalcon Payment for Cart #' . $cartModel['id']
                        ]);

                        error_log("Payment created. ID: {$payment->id}, Status: {$payment->status}");

                        if ($payment->status === 'paid') {
                            $cartItems = $this->cartService->getItemsByCartId($cartModel['id']);
                            $customerId = $cartModel['customer_id'];

                            $customer = \App\Infrastructure\Models\Customer::with('addresses')->find($customerId);
                            $addressId = null;

                            // Try to get address from source metadata first
                            $metadata = $sourceResource->attributes->metadata ?? null;
                            $scheduledDate = $metadata->scheduled_date ?? null;

                            if ($metadata && isset($metadata->customer_address_id)) {
                                $addressId = (int) $metadata->customer_address_id;
                                error_log("Using address from metadata: {$addressId}");
                            } elseif ($customer && !$customer->addresses->isEmpty()) {
                                // Fallback to customer's primary address
                                $primaryAddress = $customer->addresses->where('is_primary', true)->first();
                                if ($primaryAddress) {
                                    $addressId = $primaryAddress->id;
                                } else {
                                    $addressId = $customer->addresses->first()->id;
                                }
                                error_log("Using customer's primary address: {$addressId}");
                            }

                            if (!$addressId) {
                                throw new \Exception("Could not determine address ID for customer: {$customerId}");
                            }

                            // Create jobs for each cart item
                            $jobsCreated = 0;
                            foreach ($cartItems as $item) {
                                // Fetch the service to get its price
                                $service = $this->serviceRepository->findById($item->service_id);
                                if (!$service) {
                                    // If a service in the cart doesn't exist, skip it or handle the error
                                    continue;
                                }

                                for ($i = 0; $i < $item->quantity; $i++) {
                                    $jobData = [
                                        'customer_id'         => $customerId,
                                        'customer_address_id' => $addressId,
                                        'service_id'          => $item->service_id,
                                        'price'               => $service->base_price, // Set the price here
                                        'cart_id'             => $cartModel['id'],
                                        'status'              => 'available_for_claim',
                                        'notes'               => $item->notes,
                                        'is_priority'         => false,
                                        'scheduled_date'      => $scheduledDate
                                    ];
                                    $this->jobService->createJob($jobData);
                                    $jobsCreated++;
                                }
                            }

                            $this->cartService->changeStatus((int) $cartModel['id'], 'checked_out');
                            error_log("✓ SUCCESS: {$jobsCreated} jobs created for Cart #{$cartModel['id']}, Payment: {$payment->id}");
                        } else {
                            $this->cartService->changeStatus((int) $cartModel['id'], 'abandoned');
                            error_log("✗ FAILED: Payment status '{$payment->status}' for Cart #{$cartModel['id']}");
                        }
                    } catch (\Paymongo\Exceptions\InvalidRequestException $e) {
                        $this->cartService->changeStatus((int) $cartModel['id'], 'abandoned');
                        error_log("✗ PayMongo API Error: " . json_encode($e->getError()));
                    }
                } else {
                    error_log("⚠ WARNING: No cart found for source ID: {$sourceId}");
                }
            }

            // Always return 200 OK to acknowledge receipt
            return $this->jsonResponse(['status' => 'received']);
        } catch (\Paymongo\Exceptions\SignatureVerificationException $e) {
            error_log("✗ Signature verification failed: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Invalid signature'], 400);
        } catch (\UnexpectedValueException $e) {
            error_log("✗ Invalid payload: " . $e->getMessage());
            return $this->jsonResponse(['error' => 'Invalid payload'], 400);
        } catch (\Exception $e) {
            error_log("✗ Webhook error: " . $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine());
            return $this->jsonResponse(['error' => 'Internal server error'], 500);
        }
    }
}
