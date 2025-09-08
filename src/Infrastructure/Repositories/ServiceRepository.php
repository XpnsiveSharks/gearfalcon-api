<?php
namespace App\Infrastructure\Repositories;

use App\Infrastructure\Models\Service;

/**
 * Class ServiceRepository
 *
 * Repository for handling Service model operations.
 * Extends the base Repository for CRUD and adds
 * custom queries specific to Services.
 */
class ServiceRepository extends Repository
{
    /**
     * ServiceRepository constructor.
     *
     * @param Service $model The Service Eloquent model instance.
     */
    public function __construct(Service $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a service by its name.
     *
     * @param string $name
     * @return Service|null
     */
    public function findByName(string $name): ?Service
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Find services under a specific category.
     *
     * @param int $categoryId
     * @return \Illuminate\Support\Collection
     */
    public function findByCategory(int $categoryId)
    {
        return $this->model->where('category_id', $categoryId)->get();
    }

    /**
     * Search services by keyword in name or description.
     *
     * @param string $keyword
     * @return \Illuminate\Support\Collection
     */
    public function searchByNameOrDescription(string $keyword)
    {
        return $this->model
            ->where('name', 'LIKE', "%{$keyword}%")
            ->orWhere('description', 'LIKE', "%{$keyword}%")
            ->get();
    }

    /**
     * Get a service with its category details.
     *
     * @param int $id
     * @return Service|null
     */
    public function findWithDetails(int $id): ?Service
    {
        return $this->model->with('category')->find($id);
    }

    /**
     * Get all services with their categories.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findAllWithDetails()
    {
        return $this->model->with('category')->get();
    }

    /**
     * Get all active services.
     *
     * @return \Illuminate\Support\Collection
     */
    public function findActive()
    {
        return $this->model
            ->where('is_active', true) // or ->where('status', 'active')
            ->get();
    }
}
