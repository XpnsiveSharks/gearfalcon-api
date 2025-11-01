<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Job;

class JobRepository extends Repository
{
    public function __construct(Job $model)
    {
        parent::__construct($model);
    }

    /**
     * Find jobs by customer ID
     */
    public function findByCustomerId(int $customerId)
    {
        return $this->model->where('customer_id', $customerId)
            ->with('service') // Eager-load the service relationship
            ->get();
    }

    /**
     * Find jobs by technician ID (via job assignments)
     */
    public function findByTechnicianId(int $technicianId)
    {
        return $this->model
            ->whereHas('assignments', function ($query) use ($technicianId) {
                $query->where('technician_id', $technicianId);
            })
            ->with([
                'customer.user',
                'customerAddress',
                'service',
                'assignments.technician.user'
            ])
            ->get();
    }

    /**
     * Find jobs by status
     */
    public function findByStatus(string $status)
    {
        return $this->model
            ->where('status', $status)
            ->with(['customer.user', 'customerAddress', 'service'])
            ->get();
    }

    /**
     * Assign a technician to a job
     */
    public function assignTechnician(int $jobId, int $technicianId)
    {
        $job = $this->findById($jobId);

        if (!$job) {
            return null;
        }

        return $job->assignments()->create([
            'technician_id' => $technicianId,
        ]);
    }

    /**
     * Find a job with its related details.
     *
     * @param int $id
     * @return Job|null
     */
    public function findWithDetails(int $id): ?Job
    {
        return $this->model
            ->with([
                'customer',
                'customerAddress',
                'service',
                'assignments.technician'
            ])
            ->find($id);
    }

    /**
     * Get all jobs with their related details.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithDetails()
    {
        return $this->model
            ->with([
                'customer',
                'customerAddress',
                'service',
                'assignments.technician'
            ])
            ->get();
    }
}
