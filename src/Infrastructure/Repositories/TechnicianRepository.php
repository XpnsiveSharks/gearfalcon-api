<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Technician;

/**
 * Class TechnicianRepository
 *
 * Repository for handling Technician model operations.
 * Extends the base Repository for CRUD and adds custom
 * queries specific to Technicians (e.g. with skills).
 */
class TechnicianRepository extends Repository
{
    /**
     * TechnicianRepository constructor.
     *
     * @param Technician $model The Technician Eloquent model instance.
     */
    public function __construct(Technician $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a technician by linked User ID.
     *
     * @param string $userId
     * @return Technician|null
     */
    public function findByUserId(string $userId): ?Technician
    {
        return $this->model->where('user_id', $userId)->first();
    }

    /**
     * Find a technician and eager-load skills.
     *
     * @param int $id
     * @return Technician|null
     */
    public function findWithSkills(int $id): ?Technician
    {
        return $this->model->with('skills')->find($id);
    }

    /**
     * Get all technicians with their skills.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithSkills()
    {
        return $this->model->with('skills')->get();
    }

    /**
     * Find technicians by specialization.
     *
     * @param string $specialization
     * @return \Illuminate\Support\Collection
     */
    public function findBySpecialization(string $specialization)
    {
        return $this->model->where('specialization', 'LIKE', "%{$specialization}%")->get();
    }
}
