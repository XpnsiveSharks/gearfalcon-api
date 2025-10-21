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
                'company_name' => $data['company_name'],
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

    private function validateInput(array $data): void
    {
        if (empty($data['company_name'])) {
            throw new \InvalidArgumentException('Company name is required.');
        }

        if (empty($data['address']) || !is_array($data['address']) || empty($data['address']['street']) || empty($data['address']['city'])) {
            throw new \InvalidArgumentException('A complete address (street, city) is required.');
        }
    }
}