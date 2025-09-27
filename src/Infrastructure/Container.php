<?php
namespace App\Infrastructure;

use Dotenv\Dotenv;
use App\Infrastructure\Database\Database;

// Models
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\Cart;
use App\Infrastructure\Models\Customer;
use App\Infrastructure\Models\Quote;
use App\Infrastructure\Models\Job;
use App\Infrastructure\Models\Technician;
use App\Infrastructure\Models\ServiceCategory;
use App\Infrastructure\Models\Service;

// Repositories
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\QuoteRepository;
use App\Infrastructure\Repositories\JobRepository;
use App\Infrastructure\Repositories\TechnicianRepository;
use App\Infrastructure\Repositories\ServiceCategoryRepository;
use App\Infrastructure\Repositories\ServiceRepository;

// Services
use App\Application\Services\UserRegistrationService;
use App\Application\Customer\Services\CustomerRegistrationService;
use App\Application\Services\AuthService;
use App\Application\Services\QuoteService;
use App\Application\Services\ServiceCatalogService;
use App\Application\Admin\Services\PromotionService;
use App\Application\Services\EmailVerificationService;
use App\Application\Admin\Services\ServiceCategoryService;

// Controllers
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\Customer\QuoteController;
use App\Presentation\Controllers\TechnicianController;
use App\Presentation\Controllers\Admin\AdminController;
use App\Presentation\Controllers\CatalogController;

// Middleware
use App\Presentation\Middleware\CorsMiddleware;

// Load .env with error handling
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
} catch (\Exception $e) {
    // Log the error but don't crash in development
    error_log("Environment file not found: " . $e->getMessage());

    // Set default values for development
    if (!getenv('APP_ENV')) {
        putenv('APP_ENV=development');
    }

    // Only throw error if in production
    if (getenv('APP_ENV') === 'production') {
        throw new \RuntimeException('Environment configuration required in production');
    }
}

// Build DB config
$dbConfig = [
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'] ?? 3306,
    'dbname' => $_ENV['DB_DATABASE'],
    'user' => $_ENV['DB_USERNAME'],
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

// Initialize Database wrapper
$database = new Database($dbConfig);
$pdo = $database->getCapsule();

// Initialize repositories
$userRepository = new UserRepository(new User);
$customerRepository = new CustomerRepository(new Customer);
$cartRepository = new CartRepository(new Cart);
$quoteRepository = new QuoteRepository(new Quote);
$jobRepository = new JobRepository(new Job);
$technicianRepository = new TechnicianRepository(new Technician);
$serviceCategoryRepository = new ServiceCategoryRepository(new ServiceCategory);
$serviceRepository = new ServiceRepository(new Service);

// Services
$userRegistrationService = new UserRegistrationService($userRepository);
$customerRegistrationService = new CustomerRegistrationService($userRegistrationService, $customerRepository);
$authService = new AuthService($userRepository);
$quoteService = new QuoteService($quoteRepository, $jobRepository);
$promotionService = new PromotionService($userRepository, $technicianRepository);
$emailVerificationService = new EmailVerificationService($userRepository);
$serviceCategoryService = new ServiceCategoryService($serviceCategoryRepository);
$serviceCatalogService = new ServiceCatalogService($serviceRepository, $serviceCategoryRepository);

// Middleware
$corsMiddleware = new CorsMiddleware();
 
// Controllers
$authController = new AuthController($authService, $userRegistrationService,  $emailVerificationService);
$quoteController = new QuoteController($quoteService);
$adminController = new AdminController($serviceCategoryService);
$catalogController = new CatalogController($serviceCatalogService);
// $technicianController = new TechnicianController($promotionService); remove for now since wala pang nakalagay sa tecnh controller

// DI Container
$container = [
    CorsMiddleware::class => $corsMiddleware,
    AuthController::class => $authController,
    QuoteController::class => $quoteController,
    AdminController::class => $adminController,
    CatalogController::class => $catalogController,
    ServiceCategoryService::class => $serviceCategoryService,
    ServiceCategoryRepository::class => $serviceCategoryRepository,
    ServiceCatalogService::class => $serviceCatalogService,
    // 'App\Presentation\Controllers\TechnicianController' => $technicianController, same here wala pang nakalagay sa technician controller
];

return $container;
