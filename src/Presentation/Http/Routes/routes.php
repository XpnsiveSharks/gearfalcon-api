<?php
use App\Presentation\Http\Controllers\UserController;
use FastRoute\RouteCollector;

return function(RouteCollector $r) {
    // Static routes first
    $r->addRoute('POST', '/users/register', [UserController::class, 'register']);
    $r->addRoute('GET', '/users', [UserController::class, 'index']);
    
    // Parameterized routes last
    $r->addRoute('GET', '/users/{id}', [UserController::class, 'show']);
};

