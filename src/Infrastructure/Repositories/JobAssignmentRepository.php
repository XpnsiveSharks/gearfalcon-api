<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\JobAssignment;

/**
 * Class JobAssignmentRepository
 *
 * Repository for handling JobAssignment model operations.
 * Extends the base Repository for CRUD and adds
 * custom queries specific to JobAssignments.
 */
class JobAssignmentRepository extends Repository
{
    /**
     * JobAssignmentRepository constructor.
     *
     * @param JobAssignment $model
     */
    public function __construct(JobAssignment $model)
    {
        parent::__construct($model);
    }

    /**
     * Find all assignments for a given job.
     *
     * @param int $jobId
     * @return \Illuminate\Support\Collection
     */
    public function findByJobId(int $jobId)
    {
        return $this->model->where('job_id', $jobId)->get();
    }

    /**
     * Find all jobs assigned to a technician.
     *
     * @param int $technicianId
     * @return \Illuminate\Support\Collection
     */
    public function findByTechnicianId(int $technicianId)
    {
        return $this->model->where('technician_id', $technicianId)->get();
    }

    /**
     * Find assignment with job and technician details.
     *
     * @param int $id
     * @return JobAssignment|null
     */
    public function findWithDetails(int $id): ?JobAssignment
    {
        return $this->model->with(['job', 'technician'])->find($id);
    }
}
