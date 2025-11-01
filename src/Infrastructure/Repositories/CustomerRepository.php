<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Customer;

/**
 * Class CustomerRepository
 *
 * Repository for handling Customer model operations.
 * Extends the base Repository for CRUD and adds custom
 * queries specific to Customers.
 */
class CustomerRepository extends Repository
{
    /**
     * CustomerRepository constructor.
     *
     * @param Customer $model The Customer Eloquent model instance.
     */
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a customer by linked User ID.
     *
     * @param string $userId
     * @return Customer|null
     */
    public function findByUserId(string $userId): ?Customer
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Find a customer and eager-load addresses.
     *
     * @param int $id
     * @return Customer|null
     */
    public function findWithAddresses(int $id): ?Customer
    {
        return $this->model->with('addresses')->find($id);
    }

    /**
     * Get all customers with their related users.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithUserDetails()
    {
        return $this->model->with('user')->get();
    }

    /**
     * Find a customer by user ID with full details including user and addresses.
     *
     * @param string $userId
     * @return Customer|null
     */
    public function findWithUserDetailsByUserId(string $userId): ?Customer
    {
        return $this->model->where('user_id', $userId)->with(['user', 'addresses'])->first();
    }
}

// // Customer.php
// public function user()
// {
//     return $this->belongsTo(User::class, 'user_id');
// }

// public function addresses()
// {
//     return $this->hasMany(CustomerAddress::class, 'customer_id');
// }
