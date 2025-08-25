<?php
namespace App\Infrastructure;

use Dotenv\Dotenv;
use App\Infrastructure\Database\Database;
use App\Infrastructure\Repositories\UsersRepository;
use App\Infrastructure\Database\UnitOfWork;
use App\Application\User\RegisterUserHandler;
use App\Presentation\Http\Controllers\HomeController;
use App\Presentation\Http\Controllers\UserController;
use App\Presentation\Http\Middleware\CorsMiddleware;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../', '.env.development');
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
$pdo = $database->getConnection();

// Initialize repository
$usersRepository = new UsersRepository($pdo);

// Unit of Work
$unitOfWork = new UnitOfWork($pdo);

// Handlers / Services
$registerUserHandler = new RegisterUserHandler($usersRepository, $unitOfWork);

// Middleware
$corsMiddleware = new CorsMiddleware([
    'http://localhost:3000', // frontend URL
    'http://example.com',    // additional origins if needed
]);

// Controllers
$homeController = new HomeController($registerUserHandler);
$userController = new UserController($registerUserHandler);

// DI Container
$container = [
    CorsMiddleware::class => $corsMiddleware,
    HomeController::class => $homeController,
    UserController::class => $userController,
];

return $container;
