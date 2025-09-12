<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\CustomerAddress;

/**
 * Class CustomerAddressRepository
 *
 * Repository for handling CustomerAddress model operations.
 * Extends the base Repository for CRUD and adds custom
 * queries specific to Customer Addresses.
 */
class CustomerAddressRepository extends Repository
{
    /**
     * CustomerAddressRepository constructor.
     *
     * @param CustomerAddress $model The CustomerAddress Eloquent model instance.
     */
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all addresses for a given customer.
     *
     * @param int $customerId
     * @return \Illuminate\Support\Collection
     */
    public function findByCustomerId(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }

    /**
     * Get the primary address for a customer.
     *
     * @param int $customerId
     * @return CustomerAddress|null
     */
    public function findPrimaryAddress(int $customerId): ?CustomerAddress
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Set a given address as primary (and unset others).
     *
     * @param int $addressId
     * @param int $customerId
     * @return bool
     */
    public function setPrimaryAddress(int $addressId, int $customerId): bool
    {
        // Unset all current primary addresses for this customer
        $this->model->where('customer_id', $customerId)->update(['is_primary' => false]);

        // Set the new one as primary
        return $this->update($addressId, ['is_primary' => true]) !== null;
    }
}


// $addressRepo = new CustomerAddressRepository(new CustomerAddress());

// // Get all addresses of a customer
// $addresses = $addressRepo->findByCustomerId(1);

// // Get the primary address of a customer
// $primary = $addressRepo->findPrimaryAddress(1);

// // Set an address as primary
// $addressRepo->setPrimaryAddress(5, 1);
