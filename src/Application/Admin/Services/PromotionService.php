<?php
namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\TechnicianRepository;

class PromotionService
{
    private UserRepository $userRepository;
    private TechnicianRepository $technicianRepository;

    public function __construct(
        UserRepository $userRepository,
        TechnicianRepository $technicianRepository
    ) {
        $this->userRepository = $userRepository;
        $this->technicianRepository = $technicianRepository;
    }
    /** 
     * Promotes a user to a technician role and creates a corresponding technician profile.
     *
     * This method is used by admins to upgrade a registered customer to a technician role.
     * The user’s role is updated to 'technician' in the user repository, and a technician
     * profile is created in the technician repository, linked to the user via their ID.
     *
     * @param int $userId The ID of the user to promote.
     * @param array $technicianData Additional data for the technician profile (e.g., 'experience_years').
     * @return mixed The created technician profile.
     * @throws \Exception If the user does not exist or if the technician profile creation fails.
     */
    public function promoteToTechnician(string $userId, array $technicianData)
    {
        // Update the user's role to 'technician' in the user repository.
        $this->userRepository->update($userId, ['role' => 'technician']);

       // Create a technician profile linked to the user, including optional fields like experience years.
        return $this->technicianRepository->create([
            'user_id' => $userId,
            'experience_years' => $technicianData['experience_years'] ?? null,
        ]);
    }
}
