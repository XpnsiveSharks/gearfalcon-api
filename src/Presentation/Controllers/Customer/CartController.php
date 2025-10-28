<?php

namespace App\Presentation\Controllers\Customer;

use App\Application\Customer\Services\CartService;
use App\Infrastructure\Models\User;

class CartController
{
    private CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function getCartItems(array $request): string
    {
        /** @var User|null $user */
        $user = $request['user'] ?? null;

        if (!$user || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }

        $customerId = $user->customer->id;
        $cartItems = $this->cartService->getCart($customerId);

        return $this->jsonResponse(['data' => $cartItems]);
    }

    public function addToCart(array $request): string
    {
        /** @var User|null $user */
        $user = $request['user'] ?? null;

        if (!$user || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }

        $customerId = $user->customer->id;
        $serviceId = $request['service_id'] ?? null;
        $quantity = $request['quantity'] ?? 1;
        $notes = $request['notes'] ?? null;

        if (!$serviceId) {
            return $this->jsonResponse(['error' => 'Service ID is required'], 400);
        }

        try {
            $cartItem = $this->cartService->addToCart($customerId, (int)$serviceId, (int)$quantity, $notes);
            return $this->jsonResponse(['data' => $cartItem], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    public function removeFromCart(array $requestData): string
    {
        /** @var User|null $user */
        $user = $requestData['user'] ?? null;

        if (!$user || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }

        $cartItemId = $requestData['id'] ?? null;

        if (!$cartItemId) {
            return $this->jsonResponse(['error' => 'Cart item ID is required'], 400);
        }

        $removed = $this->cartService->removeFromCart((int)$cartItemId);

        if ($removed) {
            return $this->jsonResponse(['message' => 'Item removed from cart']);
        }

        return $this->jsonResponse(['error' => 'Item not found in cart'], 404);
    }

    public function updateCartItem(array $requestData): string
    {
        /** @var User|null $user */
        $user = $requestData['user'] ?? null;
        if (!$user || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }
        $cartItemId = $requestData['id'] ?? null;

        $updatePayload = [];
        if (isset($requestData['quantity'])) {
            $updatePayload['quantity'] = (int)$requestData['quantity'];
        }
        if (isset($requestData['notes'])) {
            $updatePayload['notes'] = $requestData['notes'];
        }

        if (!$cartItemId || empty($updatePayload)) {
            return $this->jsonResponse(['error' => 'Cart item ID and at least one of quantity or notes is required'], 400);
        }

        try {
            $updatedItem = $this->cartService->updateCartItem((int)$cartItemId, $updatePayload);
            return $this->jsonResponse(['data' => $updatedItem]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }
    public function clearCart(array $request): string
    {
        /** @var User|null $user */
        $user = $request['user'] ?? null;
        if (!$user || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }
        $customerId = $user->customer->id;
        $this->cartService->clearCart($customerId);
        return $this->jsonResponse(['message' => 'Cart cleared successfully']);
    }

    public function changeStatus(array $request): string
    {
        $cartId = $request['cart_id'] ?? null;
        $status = $request['status'] ?? null;

        if (!$cartId || !$status) {
            return $this->jsonResponse(['error' => 'Cart ID and status are required'], 400);
        }

        try {
            $updatedCart = $this->cartService->changeStatus((int)$cartId, $status);
            return $this->jsonResponse(['data' => $updatedCart]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }
}
