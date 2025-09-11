<?php
use App\Presentation\Http\Controllers\UserController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // Test home route
    $r->addRoute('GET', '/', function() {
        echo "Welcome to GearFalcon API 🚀";
    });

    // Static routes
    $r->addRoute('POST', '/users/register', [UserController::class, 'register']);
    $r->addRoute('GET', '/users', [UserController::class, 'index']);

    // Parameterized routes
    $r->addRoute('GET', '/users/{id}', [UserController::class, 'show']);
};
    