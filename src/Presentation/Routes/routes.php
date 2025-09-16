<?php
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\Customer\QuoteController;
use App\Presentation\Controllers\Admin\UserController;
use App\Presentation\Controllers\Admin\AdminController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // Test home route
    $r->addRoute('GET', '/', function() {
        echo "Welcome to GearFalcon API 🚀";
    });

    // Auth routes
    $r->addGroup('/auth', function (RouteCollector $r) {
        $r->addRoute('POST', '/login', [AuthController::class, 'login']);
        $r->addRoute('POST', '/register', [AuthController::class, 'register']);
        $r->addRoute('POST', '/logout', [AuthController::class, 'logout']);
        $r->addRoute('POST', '/verify-email', [AuthController::class, 'verifyEmail']);
        $r->addRoute('POST', '/resend-verification', [AuthController::class, 'resendVerificationCode']);
    });
    
    // Quote routes
    $r->addGroup('/quotes', function (RouteCollector $r) {
        $r->addRoute('POST', '', [QuoteController::class, 'create']);                 // create a quote
        $r->addRoute('POST', '/{id:\d+}/accept', [QuoteController::class, 'accept']); // accept a quote
        $r->addRoute('POST', '/{id:\d+}/reject', [QuoteController::class, 'reject']); // reject a quote
        $r->addRoute('GET', '/active', [QuoteController::class, 'getActive']);        // list all active quotes
    });

    // Customer quotes
    $r->addRoute('GET', '/customers/{id:\d+}/quotes', [QuoteController::class, 'getByCustomer']);

    // Admin routes
    $r->addGroup('/admin', function (RouteCollector $r) {
        // Technician routes
        $r->addRoute('POST', '/technicians/promote', [UserController::class, 'promote']);

        // Service Category routes
        $r->addGroup('/categories', function (RouteCollector $r) {
            $r->addRoute('GET', '', [AdminController::class, 'index']);            // list categories
            $r->addRoute('POST', '', [AdminController::class, 'store']);           // create category
            $r->addRoute('PUT', '/{id:\d+}', [AdminController::class, 'update']);  // update category
            $r->addRoute('DELETE', '/{id:\d+}', [AdminController::class, 'destroy']); // soft delete category
        });
    });
};