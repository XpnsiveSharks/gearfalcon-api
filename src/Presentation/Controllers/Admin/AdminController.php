<?php
namespace App\Presentation\Controllers\Admin;

use App\Application\Admin\Services\PromotionService;
use App\Application\Admin\Services\ServiceCategoryService;
use App\Application\Admin\Services\ServiceService;
use App\Application\Admin\Services\AdminSkillService;

class AdminController
{
    private ServiceCategoryService $serviceCategoryService;
    private ServiceService $serviceService;
    private PromotionService $promotionService;
    private AdminSkillService $adminSkillService;

    public function __construct(
        ServiceCategoryService $serviceCategoryService, 
        ServiceService $serviceService,
        PromotionService $promotionService,
        AdminSkillService $adminSkillService) 
        {
        $this->serviceCategoryService = $serviceCategoryService;
        $this->serviceService = $serviceService;
        $this->promotionService = $promotionService;
        $this->adminSkillService = $adminSkillService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function index(): string
    {
        $categories = $this->serviceCategoryService->getAllCategories();
        return $this->jsonResponse($categories);
    }

    public function store(array $data): string
    {
        $category = $this->serviceCategoryService->createCategory($data);
        return $this->jsonResponse($category->toArray(), 201); // Use 201 Created for new resources
    }

    public function update(array $args): string
    {
        $id = $args['id']; // Extract id from the arguments array
        $category = $this->serviceCategoryService->updateCategory($id, $args);

        if (! $category) {
            return $this->jsonResponse(['message' => 'Category not found'], 404); // Use 404 for not found
        }

        return $this->jsonResponse($category->toArray()); // The service layer should return the updated model
    }

    public function destroy(array $args): string
    {
        $id = $args['id']; // Extract id from the arguments array
        $deleted = $this->serviceCategoryService->deleteCategory($id);

        if ($deleted === null) {
            return $this->jsonResponse(['message' => 'Category not found'], 404); // Use 404 for not found
        }

        // The service layer should return the soft-deleted model
        return $this->jsonResponse([
            'message' => 'Category deleted successfully',
            'deleted_at' => $deleted->deleted_at
        ]);
    }

    public function listTechnicians(): string
    {
        $technicians = $this->promotionService->listTechnicians();
        return $this->jsonResponse($technicians->toArray());
    }

    public function listServices(): string
    {
        $services = $this->serviceService->getAllServices();
        return $this->jsonResponse($services);
    }

    public function createService(array $data): string
    {
        $service = $this->serviceService->createService($data);
        return $this->jsonResponse($service->toArray(), 201);
    }

    public function updateService(array $args): string
    {
        $id = $args['id'];
        $service = $this->serviceService->updateService($id, $args);

        if (! $service) {
            return $this->jsonResponse(['message' => 'Service not found'], 404);
        }

        return $this->jsonResponse($service->toArray());
    }

    public function deleteService(array $args): string
    {
        $id = $args['id'];
        $deleted = $this->serviceService->deleteService($id);

        if ($deleted === null) {
            return $this->jsonResponse(['message' => 'Service not found'], 404);
        }

        return $this->jsonResponse([
            'message' => 'Service deleted successfully',
            'deleted_at' => $deleted->deleted_at
        ]);
    }

    /**
     * Create a new skill.
     * POST /admin/skills
     *
     * @param array $data
     * @return string
     */
    public function createSkill(array $data): string
    {
        try {
            $skill = $this->adminSkillService->createSkill($data);
            return $this->jsonResponse($skill->toArray(), 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * List all skills.
     * GET /admin/skills
     *
     * @return string
     */
    public function listSkills(): string
    {
        $skills = $this->adminSkillService->listAllSkills();
        return $this->jsonResponse($skills->toArray());
    }

    /**
     * Update a skill.
     * PUT /admin/skills/{id}
     *
     * @param array $args
     * @return string
     */
    public function updateSkill(array $args): string
    {
        $skillId = (int) $args['id'];
        $data = $args;

        try {
            $skill = $this->adminSkillService->updateSkill($skillId, $data);
            return $this->jsonResponse($skill->toArray());
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete a skill.
     * DELETE /admin/skills/{id}
     *
     * @param array $args
     * @return string
     */
    public function deleteSkill(array $args): string
    {
        try {
            $this->adminSkillService->deleteSkill((int) $args['id']);
            return $this->jsonResponse(['message' => 'Skill deleted successfully.'], 200);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Assign a skill to a technician.
     * POST /admin/technicians/{id}/skills
     *
     * @param array $args
     * @return string
     */
    public function assignSkill(array $args): string
    {
        $technicianId = (int) $args['id'];
        $skillId = (int) ($args['skill_id'] ?? 0);
        $proficiency = $args['proficiency'] ?? 'intermediate';

        try {
            $technician = $this->adminSkillService->assignSkillToTechnician($technicianId, $skillId, $proficiency);
            return $this->jsonResponse($technician->toArray());
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove a skill from a technician.
     * DELETE /admin/technicians/{technician_id}/skills/{skill_id}
     *
     * @param array $args
     * @return string
     */
    public function removeSkill(array $args): string
    {
        $technicianId = (int) $args['technician_id'];
        $skillId = (int) $args['skill_id'];

        try {
            $technician = $this->adminSkillService->removeSkillFromTechnician($technicianId, $skillId);
            return $this->jsonResponse(['message' => 'Skill removed from technician successfully.', 'technician' => $technician->toArray()]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
