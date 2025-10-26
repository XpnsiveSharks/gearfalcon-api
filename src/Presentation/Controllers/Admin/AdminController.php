<?php
namespace App\Presentation\Controllers\Admin;

use App\Application\Admin\Services\ServiceCategoryService;
use App\Application\Admin\Services\ServiceService;

class AdminController
{
    private ServiceCategoryService $serviceCategoryService;
    private ServiceService $serviceService;

    public function __construct(
        ServiceCategoryService $serviceCategoryService,
        ServiceService $serviceService
    ) {
        $this->serviceCategoryService = $serviceCategoryService;
        $this->serviceService = $serviceService;
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
}
