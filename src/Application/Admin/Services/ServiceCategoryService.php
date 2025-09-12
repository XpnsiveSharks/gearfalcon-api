<?php

namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\ServiceCategoryRepository;
use Carbon\Carbon;

class ServiceCategoryService
{
    private ServiceCategoryRepository $serviceCategoryRepository;

    public function __construct(ServiceCategoryRepository $serviceCategoryRepository)
    {
        $this->serviceCategoryRepository = $serviceCategoryRepository;
    }

    /**
     * Create a new service category.
     *
     * @param array $data The category data (e.g., ['name' => 'Plumbing']).
     * @return mixed The created category.
     * @throws \Exception If creation fails.
     */
    public function createCategory(array $data)
    {
        return $this->serviceCategoryRepository->create($data);
    }

    /**
     * Update an existing service category.
     *
     * @param int|string $id The ID of the category to update.
     * @param array $data The updated category data.
     * @return mixed The updated category.
     * @throws \Exception If the category does not exist or update fails.
     */
    public function updateCategory($id, array $data)
    {
        return $this->serviceCategoryRepository->update($id, $data);
    }

    /**
     * Soft delete a service category by setting deleted_at.
     *
     * @param int|string $id The ID of the category to delete.
     * @return mixed The updated category with deleted_at set.
     * @throws \Exception If the category does not exist or deletion fails.
     */
    public function deleteCategory($id)
    {
        return $this->serviceCategoryRepository->update($id, [
            'deleted_at' => Carbon::now(),
        ]);
    }

    /**
     * Get all service categories (excluding soft deleted ones).
     *
     * @return array List of categories.
     */
    public function getAllCategories(): array
    {
        return $this->serviceCategoryRepository->findWhereNull('deleted_at')->toArray();
    }
}
