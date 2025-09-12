<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\ServiceCategory;

/**
 * Class ServiceCategoryRepository
 *
 * Repository for handling ServiceCategory model operations.
 * Extends the base Repository for CRUD and adds custom
 * queries specific to Service Categories.
 */
class ServiceCategoryRepository extends Repository
{
    /**
     * ServiceCategoryRepository constructor.
     *
     * @param ServiceCategory $model The ServiceCategory Eloquent model instance.
     */
    public function __construct(ServiceCategory $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a service category by its name.
     *
     * @param string $name
     * @return ServiceCategory|null
     */
    public function findByName(string $name): ?ServiceCategory
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Get a category with its related services.
     *
     * @param int $id
     * @return ServiceCategory|null
     */
    public function findWithServices(int $id): ?ServiceCategory
    {
        return $this->model->with('services')->find($id);
    }

    /**
     * Get all categories with their services.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithServices()
    {
        return $this->model->with('services')->get();
    }
}


// $categoryRepo = new ServiceCategoryRepository(new ServiceCategory());

// // Find category by name
// $hvac = $categoryRepo->findByName('HVAC');

// // Find category with services
// $categoryWithServices = $categoryRepo->findWithServices(1);

// // Get all categories with services
// $allCategories = $categoryRepo->findAllWithServices();
