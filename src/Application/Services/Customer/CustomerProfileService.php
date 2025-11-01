<?php

namespace App\Application\Services\Customer;

use App\Infrastructure\Models\User;
use App\Infrastructure\Models\Customer;
use Illuminate\Database\Capsule\Manager as DB;

class CustomerProfileService
{
    /**
     * Creates a complete customer profile including their address.
     * This operation is transactional.
     *
     * @param User $user The authenticated user object.
     * @param array $data The request data containing profile and address info.
     * @return Customer The newly created customer object.
     * @throws \Exception If the profile already exists or if data is invalid.
     */
    public function createCustomerProfile(User $user, array $data): Customer
    {
        // I-check kung may existing customer profile na ang user
        if ($user->customer()->exists()) {
            throw new \Exception('Customer profile already exists for this user.');
        }

        // I-validate ang required fields
        $this->validateInput($data);

        // Simulan ang database transaction
        return DB::transaction(function () use ($user, $data) {
            // 1. Gumawa ng record sa 'customers' table
            $customer = $user->customer()->create([
                'company_name' => $data['company_name'],
                'contact' => $data['contact'],
            ]);

            // 2. Gumawa ng record sa 'customer_addresses' table
            $customer->addresses()->create([
                'house_number' => $data['address']['house_number'],
                'street' => $data['address']['street'],
                'barangay' => $data['address']['barangay'],
                'city' => $data['address']['city'],
                'province' => $data['address']['province'],
                'region' => $data['address']['region'],
                'postal_code' => $data['address']['postal_code'],
                'is_primary' => true,
            ]);

            return $customer;
        });
    }

    /**
     * Changes the password for a given user.
     *
     * @param User $user The authenticated user object.
     * @param array $data The request data containing old and new passwords.
     * @param bool $isAdmin Flag indicating if the requesting user is an admin.
     * @return bool True on success.
     * @throws \Exception If passwords do not match or are invalid.
     */
    public function changePassword(User $user, array $data, bool $isAdmin = false): bool
    {
        // Validate required fields
        if (!$isAdmin && empty($data['old_password'])) {
            throw new \InvalidArgumentException('Old password is required.');
        }
        if (empty($data['new_password']) || empty($data['new_password_confirmation'])) {
            throw new \InvalidArgumentException('Old password, new password, and confirmation are required.');
        }

        // Verify old password, but skip this check if an admin is making the change
        if (!$isAdmin && !password_verify($data['old_password'], $user->password)) {
            throw new \Exception('The old password does not match our records.');
        }

        // Verify new password confirmation
        if ($data['new_password'] !== $data['new_password_confirmation']) {
            throw new \Exception('The new password confirmation does not match.');
        }

        // Update the user's password
        $user->password = password_hash($data['new_password'], PASSWORD_BCRYPT);
        
        return $user->save();
    }

    /**
     * Updates a customer's address.
     *
     * @param User $user The authenticated user object.
     * @param int $addressId The ID of the address to update.
     * @param array $data The new address data.
     * @return \App\Infrastructure\Models\CustomerAddress The updated address object.
     * @throws \Exception If customer profile or address does not exist, or if data is invalid.
     */
    public function changeAddress(User $user, int $addressId, array $data): \App\Infrastructure\Models\CustomerAddress
    {
        $customer = $user->customer;
        if (!$customer) {
            throw new \Exception('Customer profile not found for this user.');
        }

        $address = $customer->addresses()->find($addressId);
        if (!$address) {
            throw new \Exception('Address not found or does not belong to this customer.', 404);
        }

        // Define which fields are allowed to be updated.
        $allowedFields = [
            'house_number',
            'street',
            'barangay',
            'city',
            'province',
            'region',
            'postal_code',
            'is_primary'
        ];

        // Filter the incoming data to only include allowed fields.
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($updateData)) {
            throw new \InvalidArgumentException('No valid address fields provided for update.');
        }

        $address->update($updateData);
        return $address;
    }

    private function validateInput(array $data): void
    {
        if (empty($data['company_name'])) {
            throw new \InvalidArgumentException('Company name is required.');
        }

        if (empty($data['contact'])) {
            throw new \InvalidArgumentException('Contact number is required.');
        }

        if (empty($data['address']) || !is_array($data['address']) || empty($data['address']['street']) || empty($data['address']['city'])) {
            throw new \InvalidArgumentException('A complete address (street, city) is required.');
        }
    }
}