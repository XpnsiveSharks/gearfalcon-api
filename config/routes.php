<?php

use FastRoute\RouteCollector;
use App\Presentation\Http\Controllers\HomeController;

return function (RouteCollector $r) {
    $r->addRoute('GET', '/', [HomeController::class, 'index']);
    // include the user routes
    (require __DIR__ . '/../src/Presentation/Http/Routes/user.php')($r);

    // add other route files if needed, e.g., bookings.php, admin.php
    // (require __DIR__ . '/../src/Presentation/Http/Routes/bookings.php')($r);
};
