<?php
namespace App\Presentation\Controllers;

use App\Application\Admin\Services\PromotionService;

class TechnicianController
{
    private PromotionService $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    /**
     * Promote a user to technician.
     *
     * @param array $request
     * @return string JSON response
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
}
