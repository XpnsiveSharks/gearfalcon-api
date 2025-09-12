<?php
namespace App\Application\Services\Job;

use App\Infrastructure\Repositories\JobRepository;
use App\Infrastructure\Repositories\JobAssignmentRepository;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Carbon;
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

            // Create a new assignment with the job ID, technician ID, and current timestamp
            return $this->jobAssignmentRepository->create([
                'job_id' => $jobId,
                'technician_id' => $technicianId,
                'assigned_at' => Carbon::now(),
            ]);
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
}