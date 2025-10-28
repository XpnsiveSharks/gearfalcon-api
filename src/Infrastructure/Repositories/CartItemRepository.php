<?php

namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\CartItem;

class CartItemRepository extends Repository
{
    public function __construct(CartItem $model)
    {
        parent::__construct($model);
    }

    public function findByCartAndService(int $cartId, int $serviceId): ?CartItem
    {
        return $this->model
            ->where('cart_id', $cartId)
            ->where('service_id', $serviceId)
            ->first();
    }
}