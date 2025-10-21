<?php

namespace App\Presentation\Controllers\Customer;

use App\Application\Services\Customer\CustomerProfileService;
use App\Infrastructure\Models\User;

class CustomerController
{
    private CustomerProfileService $customerProfileService;

    public function __construct(CustomerProfileService $customerProfileService)
    {
        $this->customerProfileService = $customerProfileService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * Handles the request to complete a customer's profile.
     *
     * @param array $request The HTTP request data, including the authenticated user.
     * @return string JSON response.
     */
    public function completeProfile(array $request): string
    {
        $user = $request['user'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        try {
            $customer = $this->customerProfileService->createCustomerProfile($user, $request);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Customer profile completed successfully.',
                'customer_id' => $customer->id
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}