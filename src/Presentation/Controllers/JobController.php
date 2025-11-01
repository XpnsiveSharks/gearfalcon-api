<?php

namespace App\Presentation\Controllers;

use App\Application\Customer\Services\CartService;
use Illuminate\Support\Carbon;
use App\Application\Customer\Services\JobService;
use App\Infrastructure\Models\User;
use Exception;

class JobController
{
    private JobService $jobService;
    private CartService $cartService;

    public function __construct(JobService $jobService, CartService $cartService)
    {
        $this->jobService = $jobService;
        $this->cartService = $cartService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function createJob(array $request): string
    {
        $user = $request['user'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Manually parse the JSON request body
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        $cartId = $data['cart_id'] ?? null;

        // If a cart_id is provided, process the entire cart.
        if ($cartId) {
            try {
                $cartItems = $this->cartService->getItemsByCartId((int)$cartId);
                if ($cartItems->isEmpty()) {
                    return $this->jsonResponse(['error' => 'Cart is empty or not found.'], 404);
                }

                $jobsCreated = 0;
                foreach ($cartItems as $item) {
                    for ($i = 0; $i < $item->quantity; $i++) {
                        $jobData = [
                            'customer_id'         => $user->customer->id,
                            'customer_address_id' => $data['customer_address_id'],
                            'service_id'          => $item->service_id,
                            'cart_id'             => $cartId,
                            'status'              => 'available_for_claim',
                            'notes'               => $item->notes,
                            'is_priority'         => false,
                            'scheduled_date'      => $data['scheduled_date'] ?? Carbon::now()->toDateString(),
                        ];
                        $this->jobService->createJob($jobData);
                        $jobsCreated++;
                    }
                }

                $this->cartService->changeStatus((int)$cartId, 'checked_out');

                return $this->jsonResponse([
                    'success' => true,
                    'message' => "{$jobsCreated} jobs created successfully from cart.",
                ], 201);
            } catch (Exception $e) {
                return $this->jsonResponse(['error' => 'Failed to create jobs from cart: ' . $e->getMessage()], 400);
            }
        } else {
            // Original logic for creating a single job.
            if (empty($data['customer_address_id']) || empty($data['service_id'])) {
                return $this->jsonResponse(['error' => 'Missing required fields: customer_address_id and service_id'], 400);
            }

            $isPriority = $data['is_priority'] ?? false;

            $jobData = [
                'customer_address_id' => $data['customer_address_id'],
                'service_id' => $data['service_id'],
                'cart_id' => null,
                'notes' => $data['notes'] ?? null,
                'scheduled_date' => $data['scheduled_date'] ?? Carbon::now()->toDateString(),
                'is_priority' => $isPriority,
                'customer_id' => $user->customer->id,
                'status' => $isPriority ? 'pending_admin_assignment' : 'available_for_claim',
            ];

            try {
                $job = $this->jobService->createJob($jobData);
                return $this->jsonResponse([
                    'success' => true,
                    'message' => 'Job created successfully.',
                    'job' => $job->toArray()
                ], 201);
            } catch (Exception $e) {
                return $this->jsonResponse(['error' => $e->getMessage()], 400);
            }
        }

    }

    public function getJobsByCustomer(array $request): string
    {
        $customerId = $request['id'] ?? null;

        if (!$customerId) {
            return $this->jsonResponse(['error' => 'Customer ID is missing from the request'], 400);
        }

        try {
            $jobs = $this->jobService->getJobsByCustomerId((int)$customerId);

            if ($jobs->isEmpty()) {
                return $this->jsonResponse(['jobs' => []]);
            }

            return $this->jsonResponse(['jobs' => $jobs->toArray()]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelJob(array $request): string
    {
        $id = $request['id'] ?? null;

        if (!$id) {
            return $this->jsonResponse(['error' => 'Job ID is missing from the request'], 400);
        }

        try {
            $job = $this->jobService->updateJobStatus($id, 'cancelled');

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Job cancelled successfully.',
                'job' => $job->toArray()
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function claimJob(array $request): string
    {
        $user = $request['user'] ?? null;
        $jobId = $request['id'] ?? null;

        if (!$user instanceof User || $user->role !== 'technician' || !$user->technician) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a technician'], 401);
        }

        if (!$jobId) {
            return $this->jsonResponse(['error' => 'Job ID is missing from the request'], 400);
        }

        $technicianId = $user->technician->id;

        try {
            $job = $this->jobService->claimJob((int)$jobId, $technicianId);

            if (!$job) {
                // This case might be handled by exceptions inside the service, but as a fallback:
                return $this->jsonResponse(['error' => 'Failed to claim job'], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Job claimed successfully.',
                'job' => $job->toArray()
            ]);
        } catch (Exception $e) {
            // Return a specific status code if the job is already claimed or not available
            $statusCode = ($e->getMessage() === "This job is not available for claiming.") ? 409 : 400;
            return $this->jsonResponse(['error' => $e->getMessage()], $statusCode);
        }
    }

    public function getAvailableJobs(array $request): string
    {
        $user = $request['user'] ?? null;

        // Ensure the user is an authenticated technician or admin
        if (!$user instanceof User || !in_array($user->role, ['technician', 'admin'])) {
            return $this->jsonResponse(['error' => 'User not authenticated or not authorized'], 401);
        }

        try {
            // Call the service to get jobs with status 'available_for_claim'
            $jobs = $this->jobService->getAvailableJobs();

            // The toArray() method might not be available on a collection, so we'll let json_encode handle it.
            return $this->jsonResponse(['jobs' => $jobs]);
        } catch (Exception $e) {
            // General error handling
            return $this->jsonResponse(['error' => 'Could not retrieve available jobs: ' . $e->getMessage()], 500);
        }
    }

    public function completeJob(array $request): string
    {
        $user = $request['user'] ?? null;
        $jobId = $request['id'] ?? null;

        // 1. Authenticate and authorize user as a customer
        if (!$user instanceof User || $user->role !== 'customer' || !$user->customer) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a customer'], 401);
        }

        // 2. Validate job ID
        if (!$jobId) {
            return $this->jsonResponse(['error' => 'Job ID is missing from the request'], 400);
        }
        
        $customerId = $user->customer->id;

        try {
            // 3. Call the service to complete the job, verifying customer ownership
            $job = $this->jobService->completeJob((int)$jobId, $customerId);

            if (!$job) {
                // This case should ideally be caught by exceptions from the service,
                // but as a fallback, ensure a response is sent.
                return $this->jsonResponse(['error' => 'Failed to complete job'], 500);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Job marked as completed successfully.',
                'job' => $job->toArray()
            ]);
        } catch (Exception $e) {
            // Handle specific error messages from the service
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function getEmergencyJobs(array $request): string
    {
        $user = $request['user'] ?? null;

        // Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        try {
            // Call the service to get jobs scheduled within the next 2 days
            $jobs = $this->jobService->getEmergencyJobs();

            return $this->jsonResponse(['jobs' => $jobs->toArray()]);
        } catch (Exception $e) {
            // General error handling
            return $this->jsonResponse(['error' => 'Could not retrieve emergency jobs: ' . $e->getMessage()], 500);
        }
    }

    public function assignedJobs(array $request): string
    {
        $user = $request['user'] ?? null;

        // 1. Authenticate and authorize user as a technician
        if (!$user instanceof User || $user->role !== 'technician' || !$user->technician) {
            return $this->jsonResponse(['error' => 'User not authenticated or not a technician'], 401);
        }

        $technicianId = $user->technician->id;

        try {
            // 2. Call the service to get assigned jobs
            $jobs = $this->jobService->getAssignedJobsForTechnician($technicianId);

            if ($jobs->isEmpty()) {
                return $this->jsonResponse(['jobs' => []]);
            }

            return $this->jsonResponse(['jobs' => $jobs->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve assigned jobs: ' . $e->getMessage()], 500);
        }
    }

    public function TakenJobs(array $request): string
    {
        $user = $request['user'] ?? null;

        // 1. Ensure the user is an authenticated admin
        if (!$user instanceof User || $user->role !== 'admin') {
            return $this->jsonResponse(['error' => 'User not authenticated or not an admin'], 401);
        }

        try {
            // 2. Call the service to get all taken jobs (assignments)
            $takenJobs = $this->jobService->getTakenJobs();

            if ($takenJobs->isEmpty()) {
                return $this->jsonResponse(['assignments' => []]);
            }

            return $this->jsonResponse(['assignments' => $takenJobs->toArray()]);
        } catch (Exception $e) {
            // Handle potential errors
            return $this->jsonResponse(['error' => 'Could not retrieve taken jobs: ' . $e->getMessage()], 500);
        }
    }

    public function getTechnicianForJob(array $request): string
    {
        $jobId = $request['id'] ?? null;

        if (!$jobId) {
            return $this->jsonResponse(['error' => 'Job ID is missing from the request'], 400);
        }

        try {
            $assignment = $this->jobService->getTechnicianForJob((int)$jobId);

            if (!$assignment || !$assignment->technician) {
                return $this->jsonResponse(['error' => 'No technician assigned to this job'], 404);
            }

            $technician = $assignment->technician;
            $user = $technician->user;

            return $this->jsonResponse([
                'technician' => [
                    'id' => $technician->id,
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ]
            ]);
        } catch (Exception $e) {
            return $this->jsonResponse(['error' => 'Could not retrieve technician for the job: ' . $e->getMessage()], 500);
        }
    }
}
