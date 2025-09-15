<?php
namespace App\Presentation\Controllers\Admin;

use App\Application\Admin\Services\PromotionService;
use App\Application\Services\UserRegistrationService;

class UserController
{
    private PromotionService $promotionService;
    private UserRegistrationService $registrationService;

    public function __construct(
        PromotionService $promotionService,
        UserRegistrationService $registrationService
    ) {
        $this->promotionService = $promotionService;
        $this->registrationService = $registrationService;
    }

    /**
     * Promote a user to technician.
     */
    public function promote(array $request): string
    {
        $userId = $request['user_id'] ?? null;
        $technicianData = [
            'experience_years' => $request['experience_years'] ?? null,
        ];

        if (!$userId) {
            return json_encode(['error' => 'user_id is required']);
        }

        try {
            $technician = $this->promotionService->promoteToTechnician($userId, $technicianData);

            return json_encode([
                'success' => true,
                'message' => 'User promoted to technician successfully',
                'technician' => $technician,
            ]);
        } catch (\Exception $e) {
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle user registration.
     */
    public function register(array $request): string
    {
        try {
            $user = $this->registrationService->registerUser($request);

            return json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (\Exception $e) {
            return json_encode([
                'success' => false,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
