<?php

namespace App\Application\Customer\Services;

use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\CartItemRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use App\Infrastructure\Models\Cart;
use App\Infrastructure\Models\CartItem;
use Illuminate\Database\Capsule\Manager as DB;
use Exception;

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

    /**
     * Retrieves the active cart for a customer, including all cart items and their service details.
     *
     * @param int $customerId The ID of the customer.
     * @return array An array representing the cart with its items and service details.
     */
    public function getCart(int $customerId): array
    {
        $cart = $this->cartRepository->findActiveCart($customerId);

        if (!$cart) {
            // If no active cart, return an empty structure
            return [
                'id' => null,
                'customer_id' => $customerId,
                'status' => 'active',
                'items' => [],
                'total_price' => 0,
            ];
        }

        // Cleanup: If the cart has a payment source ID but is still active,
        // it's likely from a previous abandoned payment attempt. We can clear it
        // so a new source can be generated on the next checkout attempt.
        // We check if it's older than an hour to be safe.
        if ($cart->payment_source_id && strtotime($cart->updated_at) < time() - 3600) {
            $cart->payment_source_id = null;
            $cart->save();
        }

        // The findActiveCart method now eager-loads items.service
        $cartItems = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'cart_id' => $item->cart_id,
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'service' => $item->service ? $item->service->toArray() : null, // Include service details
            ];
        })->toArray();

        // Calculate total price based on service price
        $totalPrice = $cart->items->sum(function ($item) {
            return $item->quantity * ($item->service->base_price ?? 0);
        });

        return [
            'id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'status' => $cart->status,
            'payment_source_id' => $cart->payment_source_id, // Good to return this
            'items' => $cartItems,
            'total_price' => $totalPrice,
        ];
    }

    /**
     * Adds a service to the customer's cart or updates its quantity if already present.
     *
     * @param int $customerId
     * @param int $serviceId
     * @param int $quantity
     * @param string|null $notes
     * @return CartItem
     * @throws Exception
     */
    public function addToCart(int $customerId, int $serviceId, int $quantity = 1, ?string $notes = null): CartItem
    {
        return DB::transaction(function () use ($customerId, $serviceId, $quantity, $notes) {
            $cart = $this->cartRepository->findActiveCart($customerId);

            if (!$cart) {
                $cart = $this->cartRepository->create([
                    'customer_id' => $customerId,
                    'status' => 'active',
                ]);
            }

            $service = $this->serviceRepository->findById($serviceId);
            if (!$service) {
                throw new Exception("Service not found.");
            }

            $cartItem = $this->cartItemRepository->findByCartAndService($cart->id, $serviceId);

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->notes = $notes ?? $cartItem->notes;
                $cartItem->save();
            } else {
                $cartItem = $this->cartItemRepository->create([
                    'cart_id' => $cart->id,
                    'service_id' => $serviceId,
                    'quantity' => $quantity,
                    'notes' => $notes,
                ]);
            }
            $cartItem->load('service'); // Eager load service for the newly created/updated item
            return $cartItem;
        });
    }

    /**
     * Removes a specific item from the cart.
     *
     * @param int $cartItemId
     * @return bool
     */
    public function removeFromCart(int $cartItemId): bool
    {
        return $this->cartItemRepository->delete($cartItemId);
    }

    /**
     * Updates the quantity or notes of a cart item.
     *
     * @param int $cartItemId
     * @param array $data
     * @return CartItem
     * @throws Exception
     */
    public function updateCartItem(int $cartItemId, array $data): CartItem
    {
        $cartItem = $this->cartItemRepository->findById($cartItemId);
        if (!$cartItem) {
            throw new Exception("Cart item not found.");
        }

        if (isset($data['quantity'])) {
            $cartItem->quantity = $data['quantity'];
        }
        if (isset($data['notes'])) {
            $cartItem->notes = $data['notes'];
        }
        $cartItem->save();
        $cartItem->load('service'); // Eager load service for the updated item
        return $cartItem;
    }

    /**
     * Clears all items from a customer's active cart.
     *
     * @param int $customerId
     * @return void
     */
    public function clearCart(int $customerId): void
    {
        $cart = $this->cartRepository->findActiveCart($customerId);
        if ($cart) {
            $cart->items()->delete(); // Delete all items associated with the cart
            $this->cartRepository->delete($cart->id); // Optionally delete the cart itself
        }
    }

    /**
     * Retrieves all items for a specific cart.
     * Used by the payment webhook to create jobs after a successful checkout.
     *
     * @param int $cartId The ID of the cart.
     * @return \Illuminate\Support\Collection A collection of CartItem objects.
     * @throws Exception if the cart is not found.
     */
    public function getItemsByCartId(int $cartId)
    {
        $cart = $this->cartRepository->findWithItems($cartId);

        if (!$cart) {
            throw new Exception("Cart with ID {$cartId} not found.");
        }

        return $cart->items;
    }

    /**
     * Changes the status of a cart.
     *
     * @param int $cartId
     * @param string $status
     * @param array $additionalData Optional data to update on the cart.
     * @return Cart
     * @throws Exception
     */
    public function changeStatus(int $cartId, string $status, array $additionalData = []): Cart
    {
        $cart = $this->cartRepository->findById($cartId);
        if (!$cart) {
            throw new Exception("Cart not found.");
        }
        
        $updateData = array_merge(['status' => $status], $additionalData);
        $cart->fill($updateData);
        $cart->save();
        return $cart;
    }

    /**
     * Retrieves a cart by its associated PayMongo payment_source_id.
     *
     * @param string $sourceId The PayMongo source ID.
     * @return array|null An array representing the cart, or null if not found.
     */
    public function getCartBySourceId(string $sourceId): ?array
    {
        $cart = $this->cartRepository->findBySourceId($sourceId);
        return $cart ? $this->getCart($cart->customer_id) : null;
    }
}