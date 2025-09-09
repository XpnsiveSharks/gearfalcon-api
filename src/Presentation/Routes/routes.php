<?php
use App\Presentation\Controllers\AuthController;
use App\Presentation\Controllers\QuoteController;
use App\Presentation\Controllers\TechnicianController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // Test home route
    $r->addRoute('GET', '/', function() {
        echo "Welcome to GearFalcon API 🚀";
    });

    // Auth routes
    $r->addRoute('POST', '/auth/login', [AuthController::class, 'login']);
    $r->addRoute('POST', '/auth/register', [AuthController::class, 'register']);
    $r->addRoute('POST', '/auth/logout', [AuthController::class, 'logout']);

    // Quote routes
    $r->addRoute('POST', '/quotes', [QuoteController::class, 'create']);               // create a quote
    $r->addRoute('POST', '/quotes/{id:\d+}/accept', [QuoteController::class, 'accept']); // accept a quote
    $r->addRoute('POST', '/quotes/{id:\d+}/reject', [QuoteController::class, 'reject']); // reject a quote
    $r->addRoute('GET', '/customers/{id:\d+}/quotes', [QuoteController::class, 'getByCustomer']); // get all quotes by customer
    $r->addRoute('GET', '/quotes/active', [QuoteController::class, 'getActive']);       // list all active quotes

    // Technician routes (admin only)
    $r->addRoute('POST', '/admin/technicians/promote', [TechnicianController::class, 'promote']);
};
