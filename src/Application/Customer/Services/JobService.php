<?php
namespace App\Application\Customer\Services;

use App\Infrastructure\Repositories\JobRepository;
use App\Infrastructure\Repositories\JobAssignmentRepository;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Carbon;
use App\Infrastructure\Models\Job;
use Exception;

/**
 * JobService handles job-related tasks, such as creating jobs, assigning technicians,
 * updating job status, and fetching job details. It uses standalone Eloquent for
 * database operations and works with JobRepository and JobAssignmentRepository to
 * manage data.
 */
class JobService
{
    /**
     * @var JobRepository $jobRepository Repository for managing job data.
     */
    private JobRepository $jobRepository;

    /**
     * @var JobAssignmentRepository $jobAssignmentRepository Repository for managing job assignments.
     */
    private JobAssignmentRepository $jobAssignmentRepository;

    /**
     * Constructor to set up the service with repositories for jobs and assignments.
     *
     * @param JobRepository $jobRepository Handles job database operations.
     * @param JobAssignmentRepository $jobAssignmentRepository Handles assignment database operations.
     */
    public function __construct(
        JobRepository $jobRepository,
        JobAssignmentRepository $jobAssignmentRepository
    ) {
        $this->jobRepository = $jobRepository;
        $this->jobAssignmentRepository = $jobAssignmentRepository;
    }

    /**
     * Creates a new job with the provided data.
     *
     * @param array $jobData Data for the new job (e.g., customer ID, service ID).
     * @return mixed The created job record.
     */
    public function createJob(array $jobData)
    {
        // Use a database transaction to ensure the job is saved safely
        return DB::connection()->transaction(function () use ($jobData) {
            // Create the job using the JobRepository
            return $this->jobRepository->create($jobData);
        });
    }

    /**
     * Assigns a technician to a job.
     *
     * @param int $jobId The ID of the job to assign.
     * @param int $technicianId The ID of the technician to assign.
     * @return mixed The created assignment record.
     * @throws Exception If the job does not exist.
     */
    public function assignTechnician(int $jobId, int $technicianId)
    {
        // Use a database transaction to keep data safe
        return DB::connection()->transaction(function () use ($jobId, $technicianId) {
            // Check if the job exists
            $job = $this->jobRepository->findById($jobId);
            if (!$job) {
                throw new Exception("Job not found.");
            }

            // Update the job status to 'claimed'
            $this->jobRepository->update($jobId, ['status' => 'claimed']);

            // Create a new assignment with the job ID, technician ID, and current timestamp
            $this->jobAssignmentRepository->create([
                'job_id' => $jobId,
                'technician_id' => $technicianId,
                'assigned_at' => Carbon::now(),
            ]);

            // Return the updated job
            return $this->jobRepository->findById($jobId);
        });
    }

    /**
     * Updates the status of a job (e.g., 'pending' to 'completed').
     *
     * @param int $jobId The ID of the job to update.
     * @param string $status The new status for the job.
     * @return mixed The updated job record.
     * @throws Exception If the job does not exist.
     */
    public function updateJobStatus(int $jobId, string $status)
    {
        // Use a database transaction to ensure safe updates
        return DB::connection()->transaction(function () use ($jobId, $status) {
            // Check if the job exists
            $job = $this->jobRepository->findById($jobId);
            if (!$job) {
                throw new Exception("Job not found.");
            }

            // Update the job's status using the JobRepository
            return $this->jobRepository->update($jobId, ['status' => $status]);
        });
    }

    /**
     * Gets details for a specific job, including customer and assignment data.
     *
     * @param int $jobId The ID of the job to fetch.
     * @return mixed The job with its related data, or null if not found.
     */
    public function getJobWithDetails(int $jobId)
    {
        // Fetch the job with related data using the JobRepository
        return $this->jobRepository->findWithDetails($jobId);
    }

    /**
     * Gets all jobs with their customer and assignment details.
     *
     * @return \Illuminate\Support\Collection A list of all jobs with their related data.
     */
    public function getAllJobs()
    {
        // Fetch all jobs with related data using the JobRepository
        return $this->jobRepository->findAllWithDetails();
    }

    /**
     * Gets all jobs for a specific customer.
     *
     * @param int $customerId The ID of the customer.
     * @return \Illuminate\Support\Collection A list of jobs for the customer.
     */
    public function getJobsByCustomerId(int $customerId)
    {
        return $this->jobRepository->findByCustomerId($customerId);
    }

    /**
     * Allows a technician to claim an available job.
     *
     * @param int $jobId The ID of the job to claim.
     * @param int $technicianId The ID of the claiming technician.
     * @return mixed The updated job record.
     * @throws Exception If the job is not found or not available for claim.
     */
    public function claimJob(int $jobId, int $technicianId)
    {
        return DB::connection()->transaction(function () use ($jobId, $technicianId) {
            $job = $this->jobRepository->findById($jobId);

            if (!$job) {
                throw new Exception("Job not found.");
            }

            if ($job->status !== 'available_for_claim') {
                throw new Exception("This job is not available for claiming.");
            }

            // Create the job assignment
            $this->jobAssignmentRepository->create([
                'job_id' => $jobId,
                'technician_id' => $technicianId,
                'assigned_at' => Carbon::now(),
            ]);

            // Update the job status to 'claimed'
            return $this->jobRepository->update($jobId, ['status' => 'claimed']);
        });
    }

    /**
     * Gets all jobs that are available for technicians to claim.
     * These are jobs with the status 'available_for_claim'.
     *
     * @return \Illuminate\Support\Collection A list of available jobs with their details.
     */
    public function getAvailableJobs()
    {
        return $this->jobRepository->findByStatus('available_for_claim');
    }

    /**
     * Allows a technician to mark an assigned job as completed.
     *
     * @param int $jobId The ID of the job to be completed.
     * @param int $customerId The ID of the customer confirming completion.
     * @return mixed The updated job record.
     * @throws Exception If the job is not found, not assigned, or already completed/cancelled.
     */
    public function completeJob(int $jobId, int $customerId)
    {
        return DB::connection()->transaction(function () use ($jobId, $customerId) {
            // Fetch the job with its assignments to check if the technician is assigned
            $job = $this->jobRepository->findWithDetails($jobId);

            if (!$job) {
                throw new Exception("Job not found.");
            }
            
            // Check if the job is in a state that can be completed
            if (!in_array($job->status, ['claimed', 'in_progress'])) {
                throw new Exception("This job cannot be completed from its current status ({$job->status}).");
            }
            
            // Verify that the customer completing the job is the owner of the job
            if ($job->customer_id !== $customerId) {
                throw new Exception("You are not authorized to complete this job.");
            }

            // Soft-delete all assignments for this job
            foreach ($job->assignments as $assignment) {
                $this->jobAssignmentRepository->delete($assignment->id);
            }

            // Update the job status to 'completed' and set the completed_date
            return $this->jobRepository->update($jobId, [
                'status' => 'completed',
                'completed_date' => Carbon::now(),
            ]);
        });
    }

    /**
     * Gets all jobs that are scheduled within the next two days and are not completed or cancelled.
     * Gets all jobs that are scheduled for today and are not completed or cancelled.
     *
     * @return \Illuminate\Support\Collection A list of emergency jobs.
     */
    public function getEmergencyJobs()
    {
        $twoDaysFromNow = Carbon::now()->addDays(2);
 
        return Job::where('scheduled_date', '<=', $twoDaysFromNow)
            // ->where('scheduled_date', '>=', Carbon::now())
            ->whereNotIn('status', ['completed', 'cancelled', 'refunded', 'claimed'])
            ->with(['customer.user', 'service', 'customerAddress', 'assignments.technician.user'])
            ->get();
    }

    /**
     * Gets all jobs assigned to a specific technician.
     *
     * @param int $technicianId The ID of the technician.
     * @return \Illuminate\Support\Collection A list of jobs assigned to the technician.
     */
    public function getAssignedJobsForTechnician(int $technicianId)
    {
        // Fetch jobs assigned to the technician using the JobRepository
        return $this->jobRepository->findByTechnicianId($technicianId);
    }

    /**
     * Gets all job assignments (taken jobs).
     *
     * @return \Illuminate\Support\Collection A list of all job assignments.
     */
    public function getTakenJobs()
    {
        return $this->jobAssignmentRepository->findAllWithDetails();
    }

    /**
     * Gets the technician assigned to a specific job.
     *
     * @param int $jobId The ID of the job.
     * @return \App\Infrastructure\Models\JobAssignment|null The assignment with technician details.
     */
    public function getTechnicianForJob(int $jobId)
    {
        return $this->jobAssignmentRepository->findTechnicianByJobId($jobId);
    }

    /**
     * Cancels a job and its associated cart.
     *
     * @param int $jobId The ID of the job to cancel.
     * @return Job The updated job record.
     * @throws Exception If the job is not found or cannot be cancelled.
     */
    public function cancelJob(int $jobId): Job
    {
        return DB::connection()->transaction(function () use ($jobId) {
            $job = $this->jobRepository->findById($jobId);

            if (!$job) {
                throw new Exception("Job not found.");
            }

            // Prevent cancellation of completed jobs
            if ($job->status === 'completed') {
                throw new Exception("Completed jobs cannot be cancelled.");
            }

            // Update the job status to 'cancelled'
            return $this->jobRepository->update($jobId, ['status' => 'cancelled']);
        });
    }

    /**
     * Gets the service history (completed jobs) for a specific technician.
     *
     * @param int $technicianId The ID of the technician.
     * @return \Illuminate\Support\Collection A list of completed jobs for the technician.
     */
    public function getCompletedJobsForTechnician(int $technicianId)
    {
        return $this->jobRepository->findCompletedByTechnicianId($technicianId);
    }

    /**
     * Gets all jobs with a specific status.
     *
     * @param string $status The status to filter by.
     * @return \Illuminate\Support\Collection A list of jobs with the given status.
     */
    public function getJobsByStatus(string $status)
    {
        return $this->jobRepository->findByStatus($status);
    }

    /**
     * Updates a job's status to 'refunded'.
     *
     * @param int $jobId The ID of the job to refund.
     * @return Job The updated job record.
     * @throws Exception If the job is not found or cannot be refunded.
     */
    public function refundJob(int $jobId): Job
    {
        return DB::connection()->transaction(function () use ($jobId) {
            $job = $this->jobRepository->findById($jobId);

            if (!$job) {
                throw new Exception("Job not found.");
            }

            // Prevent refunding a job that is already refunded.
            if ($job->status === 'refunded') {
                throw new Exception("This job has already been refunded.");
            }

            return $this->jobRepository->update($jobId, ['status' => 'refunded']);
        });
    }

    public function rateJob(int $jobId, int $rating, int $customerId)
    {
        return DB::connection()->transaction(function () use ($jobId, $rating, $customerId) {
            $job = $this->jobRepository->findById($jobId);

            if (!$job) {
                throw new Exception("Job not found.");
            }

            if ($job->customer_id !== $customerId) {
                throw new Exception("You are not authorized to rate this job.");
            }

            if ($job->status !== 'completed') {
                throw new Exception("You can only rate completed jobs.");
            }

            if ($job->review !== null) {
                throw new Exception("This job has already been rated.");
            }

            $this->jobRepository->update($jobId, ['review' => $rating]);

            return $this->jobRepository->findById($jobId);
        });
    }

    public function getTechnicianAverageReview(int $technicianId): ?float
    {
        return $this->jobRepository->getAverageReviewByTechnicianId($technicianId);
    }

    public function getActiveBookingCount(): int
    {
        return $this->jobRepository->countByStatus('claimed');
    }

    public function getAverageJobReview(): ?float
    {
        return $this->jobRepository->getAverageReview();
    }

    public function getTotalRevenue(): float
    {
        $completedJobs = $this->jobRepository->getCompletedJobsWithServices();
        $totalRevenue = 0;
        foreach ($completedJobs as $job) {
            if ($job->service && $job->service->base_price) {
                $totalRevenue += $job->service->base_price;
            }
        }
        return $totalRevenue;
    }

    public function getRecentJobs(int $limit = 50)
    {
        return $this->jobRepository->findRecent($limit);
    }

    public function getTotalJobCount(): int
    {
        return $this->jobRepository->countAll();
    }
}