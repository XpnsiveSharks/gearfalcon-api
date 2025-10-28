<?php

namespace App\Application\Customer\Services;

use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\CartItemRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

class CartService
{
    private CartRepository $cartRepository;
    private CartItemRepository $cartItemRepository;
    private ServiceRepository $serviceRepository;

    public function __construct(
        CartRepository $cartRepository,
        CartItemRepository $cartItemRepository,
        ServiceRepository $serviceRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->serviceRepository = $serviceRepository;
    }

    public function addToCart(int $customerId, int $serviceId, int $quantity = 1, ?string $notes = null)
    {
        return Capsule::connection()->transaction(function () use ($customerId, $serviceId, $quantity, $notes) {
            $service = $this->serviceRepository->findById($serviceId);
            if (!$service) {
                throw new \Exception("Service not found");
            }

            $cart = $this->cartRepository->findActiveCart($customerId);
            if (!$cart) {
                $cart = $this->cartRepository->create([
                    'customer_id' => $customerId,
                    'status' => 'active'
                ]);
            }

            $existingCartItem = $this->cartItemRepository->findByCartAndService($cart->id, $serviceId);

            if ($existingCartItem) {
                $newQuantity = $existingCartItem->quantity + $quantity;
                return $this->cartItemRepository->update($existingCartItem->id, [
                    'quantity' => $newQuantity,
                    'notes' => $notes
                ]);
            }

            return $this->cartItemRepository->create([
                'cart_id' => $cart->id,
                'service_id'  => $serviceId,
                'quantity'    => $quantity,
                'notes'       => $notes,
            ]);
        });
    }

    public function removeFromCart(int $cartItemId): bool
    {
        return $this->cartItemRepository->delete($cartItemId);
    }

    public function getCart(int $customerId)
    {
        $cart = $this->cartRepository->findActiveCart($customerId);
        if ($cart) {
            return $this->cartRepository->findWithItems($cart->id);
        }
        return null;
    }

    public function updateCartItem(int $cartItemId, array $data)
    {
        $cartItem = $this->cartItemRepository->findById($cartItemId);
        if (!$cartItem) {
            throw new \Exception("Cart item not found");
        }

        if (isset($data['quantity']) && $data['quantity'] <= 0) {
            $this->removeFromCart($cartItemId);
            return null;
        }

        return $this->cartItemRepository->update($cartItemId, $data);
    }

    public function clearCart(int $customerId): void
    {
        $cart = $this->cartRepository->findActiveCart($customerId);
        if ($cart) {
            $cart->items()->delete();
        }
    }

    public function changeStatus(int $cartId, string $status)
    {
        $cart = $this->cartRepository->findById($cartId);
        if (!$cart) {
            throw new \Exception("Cart not found");
        }

        // Optional: Add validation for the status to ensure it's one of the allowed enum values.

        return $this->cartRepository->update($cartId, ['status' => $status]);
    }
}
