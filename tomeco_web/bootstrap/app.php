<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Determine which portal this instance serves
            $portalType = env('APP_PORTAL_TYPE', 'admin'); // 'admin' or 'violator'
            
            if ($portalType === 'violator') {
                // Load only violator routes for violator server instance
                Route::middleware('web')
                    ->group(base_path('routes/violator.php'));
            } else {
                // Load only admin routes for admin server instance (default)
                Route::middleware('web')
                    ->group(base_path('routes/admin.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register admin auth middleware
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
