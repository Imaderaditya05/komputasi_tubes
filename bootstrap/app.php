<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureCustomer;
use App\Http\Middleware\EnsureMitraApproved;
use App\Http\Middleware\EnsureMitraRestaurantNotAdminLocked;
use App\Http\Middleware\EnsureRestaurantUnlocked;
use App\Http\Middleware\MaintenanceMode;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'customer' => EnsureCustomer::class,
            'role' => RoleMiddleware::class,
            'restaurant.unlocked' => EnsureRestaurantUnlocked::class,
            'mitra.restaurant.not_locked' => EnsureMitraRestaurantNotAdminLocked::class,
            'mitra.approved' => EnsureMitraApproved::class,
        ]);

        $middleware->appendToGroup('web', [
            MaintenanceMode::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
