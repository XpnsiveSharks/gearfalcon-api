<?php

namespace App\Application\Services\Customer;

use App\Infrastructure\Models\User;
use App\Infrastructure\Models\Customer;
use App\Infrastructure\Models\CustomerAddress;
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
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'],
                // Pwede magdagdag ng iba pang fields dito
            ]);

            // 2. Gumawa ng record sa 'customer_addresses' table
            $customer->addresses()->create([
                'street' => $data['address']['street'],
                'city' => $data['address']['city'],
                'state' => $data['address']['state'],
                'postal_code' => $data['address']['postal_code'],
                'country' => $data['address']['country'] ?? 'PH', // Default to PH
                'is_default' => true,
            ]);

            return $customer;
        });
    }

    private function validateInput(array $data): void
    {
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['phone'])) {
            throw new \InvalidArgumentException('First name, last name, and phone are required.');
        }

        if (empty($data['address']) || !is_array($data['address']) || empty($data['address']['street']) || empty($data['address']['city'])) {
            throw new \InvalidArgumentException('A complete address (street, city) is required.');
        }
    }
}