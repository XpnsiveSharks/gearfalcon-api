<?php

namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\ServiceRepository;
use Carbon\Carbon;

class ServiceService
{
    private ServiceRepository $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    /**
     * Create a new service.
     *
     * @param array $data The service data.
     * @return mixed The created service.
     */
    public function createService(array $data)
    {
        return $this->serviceRepository->create($data);
    }

    /**
     * Update an existing service.
     *
     * @param int|string $id The ID of the service to update.
     * @param array $data The updated service data.
     * @return mixed The updated service.
     */
    public function updateService($id, array $data)
    {
        return $this->serviceRepository->update($id, $data);
    }

    /**
     * Soft delete a service by setting deleted_at.
     *
     * @param int|string $id The ID of the service to delete.
     * @return mixed The updated service with deleted_at set.
     */
    public function deleteService($id)
    {
        $service = $this->serviceRepository->findById($id);
        if (!$service) {
            return null;
        }

        $service->deleted_at = Carbon::now();
        $service->save();

        return $service->fresh();
    }

    /**
     * Get all services (excluding soft deleted ones).
     *
     * @return array List of services.
     */
    public function getAllServices(): array
    {
        return $this->serviceRepository->findWhereNull('deleted_at')->toArray();
    }
}
