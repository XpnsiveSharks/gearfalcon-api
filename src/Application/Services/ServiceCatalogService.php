<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\ServiceRepository;

class ServiceCatalogService
{
    private ServiceRepository $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function listAllServices()
    {
        return $this->serviceRepository->findAll();
    }

    public function listActiveServices()
    {
        return $this->serviceRepository->findActive();
    }

    public function getServiceDetails(int $id)
    {
        return $this->serviceRepository->findWithDetails($id);
    }

    public function searchServices(string $keyword)
    {
        return $this->serviceRepository->searchByNameOrDescription($keyword);
    }
}
