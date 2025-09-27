<?php
namespace App\Presentation\Controllers;

use App\Application\Services\ServiceCatalogService;

class CatalogController
{
    private ServiceCatalogService $catalogService;

    public function __construct(ServiceCatalogService $catalogService)
    {
        $this->catalogService = $catalogService;
    }

    public function getCategories()
    {
        $categories = $this->catalogService->getAllCategories();
        return json_encode($categories);
    }
}