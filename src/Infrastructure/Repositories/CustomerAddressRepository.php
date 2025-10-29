<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\CustomerAddress;

class CustomerAddressRepository extends Repository
{
    public function __construct(CustomerAddress $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all addresses with their associated customer and user details.
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithCustomerDetails()
    {
        return $this->model->with('customer.user')->get();
    }
}