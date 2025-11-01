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

            // Load the newly created address to return the full profile
            $customer->load('addresses');

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Customer profile completed successfully.',
                // Return the full customer object, which is more useful
                'customer' => $customer->toArray()
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handles the request to change a customer's password.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function changePassword(array $request): string
    {
        $user = $request['user'] ?? null;
        $userIdFromRoute = $request['id'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Authorization: Ensure the authenticated user is changing their own password.
        if ($user->id !== $userIdFromRoute) {
            return $this->jsonResponse(['error' => 'Forbidden. You can only change your own password.'], 403);
        }

        // Manually parse the JSON request body
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        try {
            $this->customerProfileService->changePassword($user, $data);

            return $this->jsonResponse(['success' => true, 'message' => 'Password changed successfully.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Handles the request to change a customer's address.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function changeAddress(array $request): string
    {
        $user = $request['user'] ?? null;
        $userIdFromRoute = $request['id'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Authorization: Ensure the authenticated user is changing their own details.
        // Note: The ID in the route is the user_id.
        if ($user->id !== $userIdFromRoute) {
            return $this->jsonResponse(['error' => 'Forbidden. You can only change your own address.'], 403);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        $addressId = $data['address_id'] ?? null;
        if (!$addressId) {
            return $this->jsonResponse(['error' => 'address_id is required in the request body.'], 400);
        }

        try {
            $updatedAddress = $this->customerProfileService->changeAddress($user, (int)$addressId, $data);
            return $this->jsonResponse(['success' => true, 'message' => 'Address updated successfully.', 'address' => $updatedAddress->toArray()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}