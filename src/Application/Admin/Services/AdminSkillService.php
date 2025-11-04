<?php
namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\SkillRepository;
use App\Infrastructure\Repositories\TechnicianRepository;
use App\Infrastructure\Models\Skill;
use App\Infrastructure\Models\Technician;
use Exception;

class AdminSkillService
{
    private SkillRepository $skillRepository;
    private TechnicianRepository $technicianRepository;

    public function __construct(SkillRepository $skillRepository, TechnicianRepository $technicianRepository)
    {
        $this->skillRepository = $skillRepository;
        $this->technicianRepository = $technicianRepository;
    }

    /**
     * Create a new skill.
     *
     * @param array $data
     * @return Skill
     * @throws Exception
     */
    public function createSkill(array $data): Skill
    {
        if (empty($data['name'])) {
            throw new Exception('Skill name is required.');
        }

        $skillName = $data['name'];

        // Attempt to find a skill (including soft-deleted ones) by name
        // Assuming App\Infrastructure\Models\Skill is an Eloquent model with SoftDeletes trait
        $existingSkill = Skill::withTrashed()
                               ->where('name', $skillName)
                               ->first();

        if ($existingSkill) {
            if ($existingSkill->trashed()) {
                // If the skill is soft-deleted, restore it and update its description
                $existingSkill->restore(); // Sets deleted_at to NULL
                if (isset($data['description'])) {
                    $existingSkill->description = $data['description'];
                }
                $existingSkill->save(); // Save the restored and potentially updated skill
                return $existingSkill;
            } else {
                // If an active skill with this name already exists, throw an exception
                throw new Exception("Skill with name '{$skillName}' already exists and is active.");
            }
        }

        // If no existing skill (active or soft-deleted) is found, create a new one
        return $this->skillRepository->create($data); // This will create a new skill
    }

    /**
     * Update an existing skill.
     *
     * @param int $skillId
     * @param array $data
     * @return Skill
     * @throws Exception
     */
    public function updateSkill(int $skillId, array $data): Skill
    {
        $skill = $this->skillRepository->findById($skillId);
        if (!$skill) {
            throw new Exception('Skill not found.');
        }

        if (isset($data['name'])) {
            // Check if another skill with the new name already exists
            $existingSkill = $this->skillRepository->findByName($data['name']);
            if ($existingSkill && $existingSkill->id != $skillId) {
                throw new Exception('Another skill with this name already exists.');
            }
        }

        $this->skillRepository->update($skillId, $data);
        return $this->skillRepository->findById($skillId); // Return the updated model
    }

    /**
     * Delete a skill.
     *
     * @param int $skillId
     * @return bool
     * @throws Exception
     */
    public function deleteSkill(int $skillId): bool
    {
        if (!$this->skillRepository->findById($skillId)) {
            throw new Exception('Skill not found.');
        }
        return $this->skillRepository->delete($skillId);
    }

    /**
     * List all available skills.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listAllSkills(): \Illuminate\Support\Collection
    {
        return $this->skillRepository->findAll();
    }

    /**
     * Assign a skill to a technician with a given proficiency.
     *
     * @param int $technicianId
     * @param int $skillId
     * @param string $proficiency
     * @return Technician
     * @throws Exception
     */
    public function assignSkillToTechnician(int $technicianId, int $skillId, string $proficiency = 'intermediate'): Technician
    {
        $technician = $this->technicianRepository->findById($technicianId);
        if (!$technician) {
            throw new Exception('Technician not found.');
        }

        $skill = $this->skillRepository->findById($skillId);
        if (!$skill) {
            throw new Exception('Skill not found.');
        }

        // Check if skill is already assigned to this technician
        if ($technician->skills()->where('skill_id', $skillId)->exists()) {
            throw new Exception('Technician already has this skill.');
        }

        $technician->skills()->attach($skillId, ['proficiency' => $proficiency]);
        return $technician->fresh('skills'); // Reload with updated skills
    }

    /**
     * Remove a skill from a technician.
     *
     * @param int $technicianId
     * @param int $skillId
     * @return Technician
     * @throws Exception
     */
    public function removeSkillFromTechnician(int $technicianId, int $skillId): Technician
    {
        $technician = $this->technicianRepository->findById($technicianId);
        if (!$technician) {
            throw new Exception('Technician not found.');
        }

        $skill = $this->skillRepository->findById($skillId);
        if (!$skill) {
            throw new Exception('Skill not found.');
        }

        // Detach the skill
        $detached = $technician->skills()->detach($skillId);

        if ($detached === 0) {
            throw new Exception('Technician does not have this skill.');
        }

        return $technician->fresh('skills'); // Reload with updated skills
    }

    /**
     * Syncs skills for a technician.
     *
     * @param int $technicianId
     * @param array $skills
     * @return Technician
     * @throws Exception
     */
    public function syncTechnicianSkills(int $technicianId, array $skills): Technician
    {
        $technician = $this->technicianRepository->findById($technicianId);
        if (!$technician) {
            throw new Exception('Technician not found.');
        }

        $syncData = [];
        foreach ($skills as $skill) {
            if (!isset($skill['skill_id']) || !isset($skill['proficiency'])) {
                throw new Exception("Each skill must have a 'skill_id' and 'proficiency'.");
            }
            // Ensure proficiency is one of the allowed values
            if (!in_array($skill['proficiency'], ['beginner', 'intermediate', 'expert'])) {
                throw new Exception("Proficiency must be one of: beginner, intermediate, expert.");
            }
            $syncData[$skill['skill_id']] = ['proficiency' => $skill['proficiency']];
        }

        $technician->skills()->sync($syncData);
        return $technician->fresh('skills');
    }
}