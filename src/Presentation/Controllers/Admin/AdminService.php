<?php
namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\CustomerRepository;

class AdminService
{
    private CustomerRepository $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * Gets all customers with their user details.
     *
     * @return \Illuminate\Support\Collection A list of all customers.
     */
    public function getAllCustomers()
    {
        return $this->customerRepository->findAllWithUserDetails();
    }
}