<?php

namespace App\Application\Technician\Services;

use App\Infrastructure\Repositories\TechnicianRepository;
use App\Infrastructure\Models\Technician;

class TechnicianService
{
    private TechnicianRepository $technicianRepository;

    public function __construct(TechnicianRepository $technicianRepository)
    {
        $this->technicianRepository = $technicianRepository;
    }

    /**
     * Updates a technician's profile based on their user ID.
     *
     * @param string $userId The user ID of the technician to update.
     * @param array $data The data to update (e.g., specialization, certification, experience_years).
     * @return Technician|null The updated technician model or null if not found.
     */
    public function updateTechnician(string $userId, array $data): ?Technician
    {
        $technician = $this->technicianRepository->findByUserId($userId);

        if (!$technician) {
            return null;
        }

        // The update method from the base repository handles the update and save.
        $this->technicianRepository->update($technician->id, $data);

        return $technician->fresh();
    }
       /**
     * Get detailed information for a specific technician by their user ID.
     *
     * @param string $userId
     * @return \App\Infrastructure\Models\Technician|null
     */
    public function getTechnicianDetailsByUserId(string $userId): ?\App\Infrastructure\Models\Technician
    {
        return $this->technicianRepository->findByUserIdWithDetails($userId);
    }
}