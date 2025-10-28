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
        // First, check for an existing technician record, including soft-deleted ones.
        $technician = $this->technicianRepository->findWithTrashed($userId);

        if ($technician) {
            // If a record exists, restore it if it was soft-deleted.
            if ($technician->trashed()) {
                $technician->restore();
            }

            // Update the existing record with new data.
            $technician->update([
                'specialization' => $technicianData['specialization'] ?? $technician->specialization,
                'experience_years' => $technicianData['experience_years'] ?? $technician->experience_years,
            ]);
        } else {
            // If no record exists, create a new one.
            $technician = $this->technicianRepository->create([
                'user_id' => $userId,
                'specialization' => $technicianData['specialization'] ?? null,
                'experience_years' => $technicianData['experience_years'] ?? null,
            ]);
        }

        $this->userRepository->update($userId, ['role' => 'technician']);
        return $technician;
    }

    /**
     * Demotes a technician back to a customer role.
     *
     * This method finds and deletes the technician profile associated with the user
     * and then updates the user's role back to 'customer'.
     *
     * @param string $userId The ID of the user to demote.
     * @return bool True on success, false if the technician profile was not found.
     * @throws \Exception If the user does not exist.
     */
    public function demoteFromTechnician(string $userId): bool
    {
        // Find the technician profile by user_id
        $technician = $this->technicianRepository->findByUserId($userId);

        if (!$technician) {
            // Or throw an exception if you prefer stricter handling
            return false;
        }

        // Delete the technician profile and update the user's role
        $this->technicianRepository->delete($technician->id);
        $this->userRepository->update($userId, ['role' => 'customer']);

        return true;
    }

    /**
     * Retrieves all users with the 'technician' role, including their technician profile data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listTechnicians()
    {
        // Eager load the 'technician' relationship to get profile details along with user info.
        return $this->userRepository->findByRole('technician')->load('technician');
    }
}
