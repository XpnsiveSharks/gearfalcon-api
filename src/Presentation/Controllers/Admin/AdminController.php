<?php
namespace App\Presentation\Controllers\Admin;

use App\Application\Services\AuthService;
use App\Application\Services\Customer\CustomerProfileService;
use App\Application\Admin\Services\PromotionService;
use App\Application\Admin\Services\ServiceCategoryService;
use App\Application\Admin\Services\ServiceService;
use App\Application\Technician\Services\TechnicianService;
use App\Application\Admin\Services\AdminSkillService;
use App\Infrastructure\Models\User;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Infrastructure\Repositories\CustomerAddressRepository;
use App\Infrastructure\Repositories\UserRepository;
use Exception;
class AdminController
{
    private ServiceCategoryService $serviceCategoryService;
    private ServiceService $serviceService;
    private PromotionService $promotionService;
    private AdminSkillService $adminSkillService;
    private CustomerRepository $customerRepository;
    private CustomerProfileService $customerProfileService;
    private CustomerAddressRepository $customerAddressRepository;
    private TechnicianService $technicianService;
    private AuthService $authService;
    private UserRepository $userRepository;

    public function __construct(
        ServiceCategoryService $serviceCategoryService,
        ServiceService $serviceService,
        PromotionService $promotionService,
        AdminSkillService $adminSkillService,
        CustomerRepository $customerRepository,
        CustomerAddressRepository $customerAddressRepository,
        TechnicianService $technicianService,
        CustomerProfileService $customerProfileService,
        AuthService $authService,
        UserRepository $userRepository

    )
        {
        $this->serviceCategoryService = $serviceCategoryService;
        $this->serviceService = $serviceService;
        $this->promotionService = $promotionService;
        $this->adminSkillService = $adminSkillService;
        $this->customerRepository = $customerRepository;
        $this->customerProfileService = $customerProfileService;
        $this->customerAddressRepository = $customerAddressRepository;
        $this->technicianService = $technicianService;
        $this->authService = $authService;
        $this->userRepository = $userRepository;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * List all users from the users table.
     *
     * @param array $request The HTTP request data, containing the authenticated user.
     * @return string JSON response.
     */
    public function listUsers(array $request): string
    {
        $user = $request['user'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        try {
            // 2. Call the repository to get all users
            $users = $this->userRepository->findAll();

            // 3. Return the list of users
            return $this->jsonResponse(['users' => $users->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve users: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Allows an admin to create a new user.
     *
     * @param array $request The HTTP request data, containing the authenticated admin user.
     * @return string JSON response.
     */
    public function makeUser(array $request): string
    {
        $adminUser = $request['user'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$adminUser instanceof User || $adminUser->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        // 2. Validate required fields
        $requiredFields = ['name', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->jsonResponse(['error' => "Missing required field: {$field}"], 422);
            }
        }

        // 3. Check if user already exists
        if ($this->userRepository->findByEmail($data['email'])) {
            return $this->jsonResponse(['error' => 'A user with this email already exists'], 409);
        }

        try {
            // 4. Prepare user data for creation
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_BCRYPT),
                'role' => $data['role'],
                'phone' => $data['phone'] ?? null,
                'is_verified' => 1, // Admins create verified users by default
                'email_verified_at' => date('Y-m-d H:i:s'),
            ];

            // 5. Create the user
            $newUser = $this->userRepository->create($userData);

            return $this->jsonResponse(['success' => true, 'message' => 'User created successfully', 'user' => $newUser->toArray()], 201);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Could not create user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Allows an admin to update a user's general details.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function updateUser(array $request): string
    {
        $adminUser = $request['user'] ?? null;
        $userIdToUpdate = $request['id'] ?? null;

        if (!$adminUser instanceof User || $adminUser->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        if (!$userIdToUpdate) {
            return $this->jsonResponse(['error' => 'User ID is missing from the request'], 400);
        }

        $userToUpdate = $this->userRepository->findById($userIdToUpdate);
        if (!$userToUpdate) {
            return $this->jsonResponse(['error' => 'User not found'], 404);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        $updateData = [];
        if (!empty($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (!empty($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }
        if (!empty($data['role']) && in_array($data['role'], ['admin', 'customer', 'technician'])) {
            $updateData['role'] = $data['role'];
        }

        if (empty($updateData)) {
            return $this->jsonResponse(['message' => 'No updateable fields provided.'], 400);
        }

        try {
            $updatedUser = $this->userRepository->update($userIdToUpdate, $updateData);
            return $this->jsonResponse(['success' => true, 'message' => 'User updated successfully.', 'user' => $updatedUser->toArray()]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Could not update user: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Allows an admin to delete a user.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function deleteUser(array $request): string
    {
        $adminUser = $request['user'] ?? null;
        $userIdToDelete = $request['id'] ?? null;

        if (!$adminUser instanceof User || $adminUser->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        if (!$userIdToDelete) {
            return $this->jsonResponse(['error' => 'User ID is missing from the request'], 400);
        }

        $deleted = $this->userRepository->delete($userIdToDelete);

        return $deleted
            ? $this->jsonResponse(['success' => true, 'message' => 'User deleted successfully.'])
            : $this->jsonResponse(['error' => 'User not found or could not be deleted.'], 404);
    }

    /**
     * Allows an admin to update a customer's details, including password, email, contact, and address.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function updateCustomer(array $request): string
    {
        $adminUser = $request['user'] ?? null;
        $userIdToUpdate = $request['id'] ?? null;

        if (!$adminUser instanceof User || $adminUser->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        if (!$userIdToUpdate) {
            return $this->jsonResponse(['error' => 'User ID is missing from the request'], 400);
        }

        $userToUpdate = User::with('customer.addresses')->find($userIdToUpdate);
        if (!$userToUpdate || !$userToUpdate->customer) {
            return $this->jsonResponse(['error' => 'User not found'], 404);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }
        
        $changes = [];

        try {
            // Handle name change
            if (!empty($data['name'])) {
                $userToUpdate->update(['name' => $data['name']]);
                $changes[] = 'name';
            }

            // Handle password change
            if (!empty($data['new_password'])) {
                $this->customerProfileService->changePassword($userToUpdate, $data, true);
                $changes[] = 'password';
            }

            // Handle email change
            if (!empty($data['new_email'])) {
                $this->authService->changeEmailAndRequestVerification($userToUpdate, $data['new_email']);
                $changes[] = 'email (verification required)';
            }

            // Handle contact update
            if (!empty($data['contact'])) {
                $userToUpdate->customer->update(['contact' => $data['contact']]);
                $changes[] = 'contact';
            }

            // Handle address update
            if (!empty($data['address'])) {
                $primaryAddress = $userToUpdate->customer->addresses->first();
                if ($primaryAddress) {
                    $this->customerProfileService->changeAddress($userToUpdate, $primaryAddress->id, $data['address']);
                    $changes[] = 'address';
                }
            }

            if (empty($changes)) {
                return $this->jsonResponse(['message' => 'No updateable fields provided.'], 400);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Customer details updated successfully.', 'updated_fields' => $changes]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Allows an admin to update a technician's details.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function updateTechnician(array $request): string
    {
        $adminUser = $request['user'] ?? null;
        $userIdToUpdate = $request['id'] ?? null;

        if (!$adminUser instanceof User || $adminUser->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        if (!$userIdToUpdate) {
            return $this->jsonResponse(['error' => 'Technician User ID is missing from the request'], 400);
        }

        $userToUpdate = User::with('technician')->find($userIdToUpdate);
        if (!$userToUpdate || !$userToUpdate->technician) {
            return $this->jsonResponse(['error' => 'Technician not found'], 404);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        $changes = [];

        try {
            // Update fields on the 'users' table
            if (!empty($data['name'])) {
                $userToUpdate->update(['name' => $data['name']]);
                $changes[] = 'name';
            }
            if (!empty($data['new_email'])) {
                $this->authService->changeEmailAndRequestVerification($userToUpdate, $data['new_email']);
                $changes[] = 'email (verification required)';
            }
            if (!empty($data['new_password'])) {
                $this->customerProfileService->changePassword($userToUpdate, ['new_password' => $data['new_password']], true);
                $changes[] = 'password';
            }

            // Update fields on the 'technicians' table
            $technicianData = array_filter([
                'contact' => $data['contact'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'certification' => $data['certification'] ?? null,
                'experience_years' => $data['experience_years'] ?? null,
            ]);

            if (!empty($technicianData)) {
                $userToUpdate->technician->update($technicianData);
                $changes = array_merge($changes, array_keys($technicianData));
            }

            // Sync skills and their proficiency
            if (isset($data['skills']) && is_array($data['skills'])) {
                $this->adminSkillService->syncTechnicianSkills($userToUpdate->technician->id, $data['skills']);
                $changes[] = 'skills';
            }

            if (empty($changes)) {
                return $this->jsonResponse(['message' => 'No updateable fields provided.'], 400);
            }

            return $this->jsonResponse(['success' => true, 'message' => 'Technician details updated successfully.', 'updated_fields' => $changes]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function index(): string
    {
        $categories = $this->serviceCategoryService->getAllCategories();
        return $this->jsonResponse($categories);
    }

    public function store(array $data): string
    {
        $category = $this->serviceCategoryService->createCategory($data);
        return $this->jsonResponse($category->toArray(), 201); // Use 201 Created for new resources
    }

    public function update(array $args): string
    {
        $id = $args['id']; // Extract id from the arguments array
        $category = $this->serviceCategoryService->updateCategory($id, $args);

        if (! $category) {
            return $this->jsonResponse(['message' => 'Category not found'], 404); // Use 404 for not found
        }

        return $this->jsonResponse($category->toArray()); // The service layer should return the updated model
    }

    public function destroy(array $args): string
    {
        $id = $args['id']; // Extract id from the arguments array
        $deleted = $this->serviceCategoryService->deleteCategory($id);

        if ($deleted === null) {
            return $this->jsonResponse(['message' => 'Category not found'], 404); // Use 404 for not found
        }

        // The service layer should return the soft-deleted model
        return $this->jsonResponse([
            'message' => 'Category deleted successfully',
            'deleted_at' => $deleted->deleted_at
        ]);
    }

    public function listTechnicians(): string
    {
        $technicians = $this->promotionService->listTechnicians();
        return $this->jsonResponse($technicians->toArray());
    }

    public function getTechnicianDetails(array $request): string
    {
        $user = $request['user'] ?? null;
        $technicianUserId = $request['id'] ?? null;

        // 1. Ensure the user is authenticated
        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Authorization Check: Allow if the user is an admin OR a technician requesting their own details.
        $isOwner = ($user->role === 'technician' && $user->id === $technicianUserId);
        $isAdmin = ($user->role === 'admin');
        if (!$isAdmin && !$isOwner) {
            return $this->jsonResponse(['error' => 'Forbidden. You do not have permission to view these details.'], 403);
        }

        if (!$technicianUserId) {
            return $this->jsonResponse(['error' => 'Technician user ID is missing'], 400);
        }

        try {
            // 2. Call the service to get technician details
            $technician = $this->technicianService->getTechnicianDetailsByUserId($technicianUserId);

            if (!$technician) {
                return $this->jsonResponse(['error' => 'Technician not found'], 404);
            }

            // 3. Return the technician details
            return $this->jsonResponse(['technician' => $technician->toArray()]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Could not retrieve technician details: ' . $e->getMessage()], 500);
        }
    }

    public function listCustomers(array $request): string
    {
        $user = $request['user'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        try {
            // 2. Call the repository to get all customers
            $customers = $this->customerRepository->findAllWithUserDetails();

            if ($customers->isEmpty()) {
                return $this->jsonResponse(['customers' => []]);
            }

            // 3. Return the list of customers
            return $this->jsonResponse(['customers' => $customers->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve customers: ' . $e->getMessage()], 500);
        }
    }

    public function getCustomerDetails(array $request): string
    {
        $user = $request['user'] ?? null;
        $customerUserId = $request['id'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        if (!$customerUserId) {
            return $this->jsonResponse(['error' => 'Customer user ID is missing'], 400);
        }

        try {
            // 2. Call the repository to get customer details by user ID
            $customer = $this->customerRepository->findWithUserDetailsByUserId($customerUserId);

            if (!$customer) {
                return $this->jsonResponse(['error' => 'Customer not found'], 404);
            }

            // 3. Return the customer details
            return $this->jsonResponse(['customer' => $customer->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve customer details: ' . $e->getMessage()], 500);
        }
    }

    public function listCustomerAddresses(array $request): string
    {
        $user = $request['user'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        try {
            // 2. Call the repository to get all customer addresses
            $addresses = $this->customerAddressRepository->findAllWithCustomerDetails();

            if ($addresses->isEmpty()) {
                return $this->jsonResponse(['addresses' => []]);
            }

            // 3. Return the list of addresses
            return $this->jsonResponse(['addresses' => $addresses->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve customer addresses: ' . $e->getMessage()], 500);
        }
    }

    public function listServices(): string
    {
        $services = $this->serviceService->getAllServices();
        return $this->jsonResponse($services);
    }

    public function createService(array $data): string
    {
        $service = $this->serviceService->createService($data);
        return $this->jsonResponse($service->toArray(), 201);
    }

    public function updateService(array $args): string
    {
        $id = $args['id'];
        $service = $this->serviceService->updateService($id, $args);

        if (! $service) {
            return $this->jsonResponse(['message' => 'Service not found'], 404);
        }

        return $this->jsonResponse($service->toArray());
    }

    public function deleteService(array $args): string
    {
        $id = $args['id'];
        $deleted = $this->serviceService->deleteService($id);

        if ($deleted === null) {
            return $this->jsonResponse(['message' => 'Service not found'], 404);
        }

        return $this->jsonResponse([
            'message' => 'Service deleted successfully',
            'deleted_at' => $deleted->deleted_at
        ]);
    }

    /**
     * Create a new skill.
     * POST /admin/skills
     *
     * @param array $data
     * @return string
     */
    public function createSkill(array $data): string
    {
        try {
            $skill = $this->adminSkillService->createSkill($data);
            return $this->jsonResponse($skill->toArray(), 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * List all skills.
     * GET /admin/skills
     *
     * @return string
     */
    public function listSkills(): string
    {
        $skills = $this->adminSkillService->listAllSkills();
        return $this->jsonResponse($skills->toArray());
    }

    /**
     * Update a skill.
     * PUT /admin/skills/{id}
     *
     * @param array $args
     * @return string
     */
    public function updateSkill(array $args): string
    {
        $skillId = (int) $args['id'];
        $data = $args;

        try {
            $skill = $this->adminSkillService->updateSkill($skillId, $data);
            return $this->jsonResponse($skill->toArray());
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a skill.
     * DELETE /admin/skills/{id}
     *
     * @param array $args
     * @return string
     */
    public function deleteSkill(array $args): string
    {
        try {
            $this->adminSkillService->deleteSkill((int) $args['id']);
            return $this->jsonResponse(['message' => 'Skill deleted successfully.'], 200);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Assign a skill to a technician.
     * POST /admin/technicians/{id}/skills
     *
     * @param array $args
     * @return string
     */
    public function assignSkill(array $args): string
    {
        $technicianId = (int) $args['id'];
        $skillId = (int) ($args['skill_id'] ?? 0);
        $proficiency = $args['proficiency'] ?? 'intermediate';

        try {
            $technician = $this->adminSkillService->assignSkillToTechnician($technicianId, $skillId, $proficiency);
            return $this->jsonResponse($technician->toArray());
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a skill from a technician.
     * DELETE /admin/technicians/{technician_id}/skills/{skill_id}
     *
     * @param array $args
     * @return string
     */
    public function removeSkill(array $args): string
    {
        $technicianId = (int) $args['technician_id'];
        $skillId = (int) $args['skill_id'];

        try {
            $technician = $this->adminSkillService->removeSkillFromTechnician($technicianId, $skillId);
            return $this->jsonResponse(['message' => 'Skill removed from technician successfully.', 'technician' => $technician->toArray()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
