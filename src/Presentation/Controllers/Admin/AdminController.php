<?php
namespace App\Presentation\Controllers\Admin;

use App\Application\Admin\Services\ServiceCategoryService;

class AdminController
{
    private ServiceCategoryService $serviceCategoryService;

    public function __construct(ServiceCategoryService $serviceCategoryService)
    {
        $this->serviceCategoryService = $serviceCategoryService;
    }

    public function index()
    {
        $categories = $this->serviceCategoryService->getAllCategories();
        return json_encode($categories);
    }

    public function store(array $data)
    {
        $category = $this->serviceCategoryService->createCategory($data);
        return json_encode($category);
    }

    public function update($id, array $data)
    {
        $category = $this->serviceCategoryService->updateCategory($id, $data);

        if (!$category) {
            return json_encode(['message' => 'Category not found']);
        }

        return json_encode($category);
    }

    public function destroy($id)
    {
        $deleted = $this->serviceCategoryService->deleteCategory($id);

        if ($deleted === null) {
            return json_encode(['message' => 'Category not found']);
        }

        return json_encode([
            'message' => 'Category deleted successfully',
            'deleted_at' => $deleted->deleted_at
        ]);
    }
}
