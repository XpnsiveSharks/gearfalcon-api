<?php
declare(strict_types=1);

namespace App\Presentation\Http\Routes;

use FastRoute\RouteCollector;
use App\Presentation\Http\Controllers\UserController;

return function(RouteCollector $r) {
    $r->addRoute('GET', '/users', [UserController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UserController::class, 'show']);
    $r->addRoute('POST', '/users', [UserController::class, 'store']);
};
