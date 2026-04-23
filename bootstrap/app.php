<?php

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
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'customer' => \App\Http\Middleware\EnsureCustomer::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'restaurant.unlocked' => \App\Http\Middleware\EnsureRestaurantUnlocked::class,
        ]);

        $middleware->appendToGroup('web', [
            \App\Http\Middleware\MaintenanceMode::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhook/midtrans',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
