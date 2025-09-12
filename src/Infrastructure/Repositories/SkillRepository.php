<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Skill;

/**
 * Class SkillRepository
 *
 * Repository for handling Skill model operations.
 * Extends the base Repository for CRUD and adds
 * custom queries specific to Skills.
 */
class SkillRepository extends Repository
{
    /**
     * SkillRepository constructor.
     *
     * @param Skill $model The Skill Eloquent model instance.
     */
    public function __construct(Skill $model)
    {
        parent::__construct($model);
    }

    /**
     * Find all skills by technician ID.
     *
     * @param int $technicianId
     * @return \Illuminate\Support\Collection
     */
    public function findByTechnicianId(int $technicianId)
    {
        return $this->model->where('technician_id', $technicianId)->get();
    }

    /**
     * Find a skill by name.
     *
     * @param string $name
     * @return Skill|null
     */
    public function findByName(string $name): ?Skill
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Get all skills with related technician.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithTechnicians()
    {
        return $this->model->with('technician')->get();
    }
}
