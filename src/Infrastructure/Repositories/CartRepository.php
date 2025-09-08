<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Cart;

/**
 * Class CartRepository
 *
 * Repository for handling Cart model operations.
 * Extends the base Repository for CRUD and adds
 * custom queries specific to Carts.
 */
class CartRepository extends Repository
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    /**
     * Find all carts for a customer.
     */
    public function findByCustomerId(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }

    /**
     * Find active cart for a customer.
     */
    public function findActiveCart(int $customerId): ?Cart
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Find a cart with its items.
     */
    public function findWithItems(int $id): ?Cart
    {
        return $this->model->with('items')->find($id);
    }

    /**
     * Get all carts with details (items + customer).
     */
    public function findAllWithDetails()
    {
        return $this->model->with(['items', 'customer'])->get();
    }

    /**
     * Find a cart item by customer and service.
     */
    public function findByCustomerAndService(int $customerId, int $serviceId): ?Cart
    {
        return $this->model
            ->where('customer_id', $customerId)
            ->where('service_id', $serviceId)
            ->first();
    }

    /**
     * Get all items in a customer's cart.
     */
    public function findByCustomer(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)->get();
    }
}
