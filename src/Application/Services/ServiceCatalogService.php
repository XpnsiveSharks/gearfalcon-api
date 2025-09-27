<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\ServiceRepository;
use App\Infrastructure\Repositories\ServiceCategoryRepository;

class ServiceCatalogService
{
    private ServiceRepository $serviceRepository;
    private ServiceCategoryRepository $categoryRepository;

    public function __construct(
        ServiceRepository $serviceRepository,
        ServiceCategoryRepository $categoryRepository
    ) {
        $this->serviceRepository = $serviceRepository;
        $this->categoryRepository = $categoryRepository;
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

    public function getAllCategories(): array
    {
        return $this->categoryRepository->findWhereNull('deleted_at')->toArray();
    }
}
 