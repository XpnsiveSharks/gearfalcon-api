<?php

namespace App\Presentation\Controllers\Technician;

use App\Application\Technician\Services\TechnicianService;
use App\Infrastructure\Models\User;

class TechnicianController
{
    private TechnicianService $technicianService;

    public function __construct(TechnicianService $technicianService)
    {
        $this->technicianService = $technicianService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    /**
     * Handle request to update a technician's profile.
     *
     * @param array $request
     * @return string
     */
    public function updateTechnician(array $request): string
    {
        /** @var User|null $loggedInUser */
        $loggedInUser = $request['user'] ?? null;
        $technicianUserIdToUpdate = $request['id'] ?? null;

        if (!$loggedInUser) {
            return $this->jsonResponse(['error' => 'Authentication error.'], 401);
        }

        // Authorization Check:
        // Allow if the user is an admin OR if the user is updating their own profile.
        $isOwner = ($loggedInUser->id === $technicianUserIdToUpdate);
        $isAdmin = ($loggedInUser->role === 'admin');
        if (!$isOwner && !$isAdmin) {
            return $this->jsonResponse(['error' => 'Forbidden. You do not have permission to update this profile.'], 403);
        }

        $updateData = [
            'specialization' => $request['specialization'] ?? null,
            'certification' => $request['certification'] ?? null,
            'experience_years' => $request['experience_years'] ?? null,
        ];

        $technician = $this->technicianService->updateTechnician($technicianUserIdToUpdate, array_filter($updateData, fn($value) => $value !== null));

        if (!$technician) {
            return $this->jsonResponse(['error' => 'Technician not found.'], 404);
        }

        return $this->jsonResponse($technician->toArray());
    }
}
