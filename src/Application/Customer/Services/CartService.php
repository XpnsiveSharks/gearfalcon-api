<?php
namespace App\Application\Services\Cart;

use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * CartService handles all cart-related operations for a customer, such as adding,
 * removing, retrieving, and clearing items in the cart. It interacts with the
 * CartRepository and ServiceRepository to manage cart data and ensure services
 * exist before adding them to the cart.
 */
class CartService
{
    /**
     * @var CartRepository $cartRepository Repository for accessing and managing cart data.
     */
    private CartRepository $cartRepository;

    /**
     * @var ServiceRepository $serviceRepository Repository for accessing service data.
     */
    private ServiceRepository $serviceRepository;

    /**
     * Constructor to initialize the CartService with required repositories.
     *
     * @param CartRepository $cartRepository Repository for cart-related database operations.
     * @param ServiceRepository $serviceRepository Repository for service-related database operations.
     */
    public function __construct(CartRepository $cartRepository, ServiceRepository $serviceRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Adds a service to a customer's cart with a specified quantity.
     * If the service is already in the cart, it updates the quantity.
     * If not, it creates a new cart item. Uses a database transaction to ensure
     * data consistency.
     *
     * @param int $customerId The ID of the customer whose cart is being updated.
     * @param int $serviceId The ID of the service to add to the cart.
     * @param int $quantity The number of units of the service to add (default is 1).
     * @return mixed The updated or newly created cart item.
     * @throws \Exception If the service does not exist.
     */
    public function addToCart(int $customerId, int $serviceId, int $quantity = 1)
    {
        return Capsule::connection()->transaction(function () use ($customerId, $serviceId, $quantity) {
            // Check if the service exists in the database
            $service = $this->serviceRepository->findById($serviceId);

            if (!$service) {
                throw new \Exception("Service not found");
            }

            // Check if the service is already in the customer's cart
            $existingCartItem = $this->cartRepository->findByCustomerAndService($customerId, $serviceId);

            if ($existingCartItem) {
                // If the item exists, increase its quantity
                $newQuantity = $existingCartItem->quantity + $quantity;
                return $this->cartRepository->update($existingCartItem->id, [
                    'quantity' => $newQuantity
                ]);
            }

            // If the item doesn't exist, create a new cart entry
            return $this->cartRepository->create([
                'customer_id' => $customerId,
                'service_id'  => $serviceId,
                'quantity'    => $quantity,
            ]);
        });
    }

    /**
     * Removes a specific service from a customer's cart.
     *
     * @param int $customerId The ID of the customer whose cart is being updated.
     * @param int $serviceId The ID of the service to remove from the cart.
     * @return bool True if the item was removed, false if the item was not found.
     */
    public function removeFromCart(int $customerId, int $serviceId): bool
    {
        // Find the cart item for the customer and service
        $cartItem = $this->cartRepository->findByCustomerAndService($customerId, $serviceId);

        if (!$cartItem) {
            return false; // Return false if the item isn't in the cart
        }

        // Delete the cart item
        return $this->cartRepository->delete($cartItem->id);
    }

    /**
     * Retrieves all items in a customer's cart.
     *
     * @param int $customerId The ID of the customer whose cart is being retrieved.
     * @return mixed A collection of cart items for the customer.
     */
    public function getCart(int $customerId)
    {
        // Fetch all cart items for the customer
        return $this->cartRepository->findByCustomer($customerId);
    }

    /**
     * Clears all items from a customer's cart.
     *
     * @param int $customerId The ID of the customer whose cart is being cleared.
     * @return void
     */
    public function clearCart(int $customerId): void
    {
        // Get all cart items for the customer
        $cartItems = $this->cartRepository->findByCustomer($customerId);

        // Loop through and delete each item
        foreach ($cartItems as $item) {
            $this->cartRepository->delete($item->id);
        }
    }
}