<?php
namespace App\Application\Customer\Services;

use App\Infrastructure\Repositories\CustomerRepository;
use App\Application\Services\UserRegistrationService;
use App\Infrastructure\Models\Customer;

/**
 * CustomerRegistrationService handles the registration of new customers.
 * It creates a user account using UserRegistrationService and then links
 * a customer record to that user in the database.
 */
class CustomerRegistrationService
{
    /**
     * @var UserRegistrationService $userRegistrationService Service for registering user accounts.
     */
    private UserRegistrationService $userRegistrationService;

    /**
     * @var CustomerRepository $customerRepository Repository for managing customer data.
     */
    private CustomerRepository $customerRepository;

    /**
     * Constructor to initialize the CustomerRegistrationService with required dependencies.
     *
     * @param UserRegistrationService $userRegistrationService Service to handle user account creation.
     * @param CustomerRepository $customerRepository Repository to manage customer data in the database.
     */
    public function __construct(
        UserRegistrationService $userRegistrationService,
        CustomerRepository $customerRepository
    ) {
        $this->userRegistrationService = $userRegistrationService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Registers a new customer by creating a user account and linking a customer record to it.
     *
     * This method first creates a user with the provided name, email, password, and optional phone.
     * It assigns the 'customer' role to the user. Then, it creates a customer record linked to the user,
     * including an optional company name.
     *
     * @param array $data Customer data including name, email, password, phone (optional), and company_name (optional).
     * @return Customer The newly created customer record.
     */
    public function registerCustomer(array $data): Customer
    {
        // Step 1: Create a user account with the provided details
        $user = $this->userRegistrationService->registerUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'customer',
            'phone' => $data['phone'] ?? null,
        ]);

        // Step 2: Create a customer record linked to the user
        return $this->customerRepository->create([
            'user_id' => $user->id,
            'company_name' => $data['company_name'] ?? null,
        ]);
    }
}