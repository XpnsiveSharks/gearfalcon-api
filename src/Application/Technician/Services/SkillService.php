<?php
namespace App\Application\Technician\Services;

use App\Infrastructure\Repositories\SkillRepository;

class SkillService
{
    private SkillRepository $skillRepository;

    public function __construct(SkillRepository $skillRepository)
    {
        $this->skillRepository = $skillRepository;
    }

    public function addSkill(int $technicianId, string $skillName)
    {
        return $this->skillRepository->create([
            'technician_id' => $technicianId,
            'name' => $skillName,
        ]);
    }
}
