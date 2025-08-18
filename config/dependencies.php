<?php
declare(strict_types=1);

use App\Infrastructure\Container;
use App\Presentation\Http\Controllers\UserController;
use App\Application\Services\UserService;

return function(Container $container) {
    // Register services
    $container->set(UserService::class, function() {
        return new UserService();
    });

    // Register controllers
    $container->set(UserController::class, function(Container $c) {
        return new UserController(
            $c->get(UserService::class)
        );
    });
};
