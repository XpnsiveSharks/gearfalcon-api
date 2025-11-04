<?php
namespace App\Infrastructure;

use Dotenv\Dotenv;
use App\Infrastructure\Database\Database;

// Models
use App\Infrastructure\Models\User;
use App\Infrastructure\Models\Cart;
use App\Infrastructure\Models\CartItem;
use App\Infrastructure\Models\CustomerAddress;
use App\Infrastructure\Models\Customer;
use App\Infrastructure\Models\Quote;
use App\Infrastructure\Models\Job;
use App\Infrastructure\Models\Technician;
use App\Infrastructure\Models\JobAssignment; // Added for JobAssignmentRepository
use App\Infrastructure\Models\ServiceCategory;
use App\Infrastructure\Models\Service;
use App\Infrastructure\Models\Skill;

// Repositories
use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Repositories\CustomerRepository;
use App\Infrastructure\Repositories\CustomerAddressRepository;
use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\CartItemRepository;
use App\Infrastructure\Repositories\QuoteRepository;
use App\Infrastructure\Repositories\JobRepository;
use App\Infrastructure\Repositories\TechnicianRepository;
use App\Infrastructure\Repositories\JobAssignmentRepository; // Added for JobService
use App\Infrastructure\Repositories\ServiceCategoryRepository;
use App\Infrastructure\Repositories\SkillRepository;
use App\Infrastructure\Repositories\ServiceRepository;
use App\Infrastructure\Repositories\RefundRepository;

// Services
use App\Application\Services\UserRegistrationService;
use App\Application\Customer\Services\CustomerRegistrationService;
use App\Application\Services\AuthService;
use App\Application\Customer\Services\JobService; // Added for JobController and QuoteService
use App\Application\Customer\Services\CartService;
use App\Application\Services\QuoteService;
use App\Application\Services\ServiceCatalogService;
use App\Application\Admin\Services\PromotionService;
use App\Application\Services\EmailVerificationService;
use App\Application\Admin\Services\ServiceCategoryService;
use App\Application\Admin\Services\ServiceService;
use App\Application\Admin\Services\AdminSkillService;
use App\Application\Services\Customer\CustomerProfileService;
use App\Application\Technician\Services\TechnicianService;
use App\Application\Customer\Services\RefundService;


// Controllers
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\Customer\CartController;
use App\Presentation\Controllers\JobController; // Added for JobController
use App\Presentation\Controllers\Customer\QuoteController;
use App\Presentation\Controllers\Admin\AdminController;
use App\Presentation\Controllers\Admin\UserController;
use App\Presentation\Controllers\Technician\TechnicianController;
use App\Presentation\Controllers\CatalogController;
use App\Presentation\Controllers\Customer\CustomerController;
use App\Presentation\Controllers\PaymentController; // ⬇️ ADDED THIS LINE (Step 1 of 3)

// Middleware
use App\Presentation\Middleware\CorsMiddleware;
use App\Presentation\Middleware\AuthMiddleware;

// Load .env with error handling
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env');
$dotenv->load();

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
$customerAddressRepository = new CustomerAddressRepository(new CustomerAddress);
$cartRepository = new CartRepository(new Cart);
$cartItemRepository = new CartItemRepository(new CartItem);
$quoteRepository = new QuoteRepository(new Quote);
$jobRepository = new JobRepository(new Job);
$jobAssignmentRepository = new JobAssignmentRepository(new JobAssignment); // Initialized JobAssignmentRepository
$technicianRepository = new TechnicianRepository(new Technician);
$serviceCategoryRepository = new ServiceCategoryRepository(new ServiceCategory);
$skillRepository = new SkillRepository(new Skill);
$serviceRepository = new ServiceRepository(new Service);

// Initialize PHPMailer

// Housekeeping: delete unverified users whose verification expired (>5 minutes)
try {
    $deletedCount = $userRepository->deleteExpiredUnverifiedUsers(5);
    if ($deletedCount > 0) {
        error_log("Cleanup: deleted {$deletedCount} expired unverified user(s)");
    }
} catch (\Throwable $e) {
    // Do not block app if cleanup fails
    error_log('Cleanup error (deleteExpiredUnverifiedUsers): ' . $e->getMessage());
}

// Services
$userRegistrationService = new UserRegistrationService($userRepository);
$customerRegistrationService = new CustomerRegistrationService($userRegistrationService, $customerRepository);
$authService = new AuthService($userRepository, new EmailVerificationService($userRepository));
$jobService = new JobService($jobRepository, $jobAssignmentRepository); // Initialized JobService
$cartService = new CartService($cartRepository, $cartItemRepository, $serviceRepository);
$quoteService = new QuoteService($quoteRepository, $jobRepository, $jobService); // Corrected QuoteService instantiation
$promotionService = new PromotionService($userRepository, $technicianRepository);
$emailVerificationService = new EmailVerificationService($userRepository);
$serviceCategoryService = new ServiceCategoryService($serviceCategoryRepository);
$serviceService = new ServiceService($serviceRepository);
$adminSkillService = new AdminSkillService($skillRepository, $technicianRepository);
$serviceCatalogService = new ServiceCatalogService($serviceRepository, $serviceCategoryRepository);
$customerProfileService = new CustomerProfileService($userRepository);
$technicianService = new TechnicianService($technicianRepository);

// Middleware
$corsMiddleware = new CorsMiddleware();
// Auth middleware with JWT secret
$authMiddleware = new AuthMiddleware($_ENV['JWT_SECRET'] ?? '');

// Controllers
$authController = new AuthController($authService, $userRegistrationService, $emailVerificationService);
$cartController = new CartController($cartService);
$quoteController = new QuoteController($quoteService);
$jobController = new JobController($jobService, $cartService);
$adminController = new AdminController($serviceCategoryService, $serviceService, $promotionService, $adminSkillService, $customerRepository, $customerAddressRepository, $technicianService, $customerProfileService, $authService, $userRepository);
$catalogController = new CatalogController($serviceCatalogService);
$userController = new UserController($promotionService, $userRegistrationService);
$customerController = new CustomerController($customerProfileService);
$technicianController = new TechnicianController($technicianService);

// ⬇️ ADDED THIS SECTION (Step 2 of 3) ⬇️
// Initialized PaymentController with its 3 dependencies
$paymentController = new PaymentController(
    $cartService,
    $jobService,
    $customerAddressRepository
);
// ⬆️ END ADDED SECTION ⬆️

// DI Container
$container = [
  CorsMiddleware::class => $corsMiddleware,
  AuthMiddleware::class => $authMiddleware,
  AuthController::class => $authController,
  CartController::class => $cartController,
  QuoteController::class => $quoteController,
  JobController::class => $jobController, // Added JobController to the container
  AdminController::class => $adminController,
  UserController::class => $userController,
  CatalogController::class => $catalogController,
  ServiceCategoryService::class => $serviceCategoryService,
  ServiceService::class => $serviceService,
  ServiceCategoryRepository::class => $serviceCategoryRepository,
  ServiceCatalogService::class => $serviceCatalogService,
  CustomerController::class => $customerController,
  TechnicianController::class => $technicianController,
    PaymentController::class => $paymentController, // ⬇️ ADDED THIS LINE (Step 3 of 3)
];

return $container;