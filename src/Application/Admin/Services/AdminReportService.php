<?php

namespace App\Application\Admin\Services;

use App\Infrastructure\Repositories\JobRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use Exception;

class AdminReportService
{
    private JobRepository $jobRepository;
    private ServiceRepository $serviceRepository;

    public function __construct(JobRepository $jobRepository, ServiceRepository $serviceRepository)
    {
        $this->jobRepository = $jobRepository;
        $this->serviceRepository = $serviceRepository;
    }
}