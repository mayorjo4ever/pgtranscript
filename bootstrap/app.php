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
    ->withMiddleware(function (Middleware $middleware) {
           $middleware->alias([
                'admin.active' => \App\Http\Middleware\UpdateAdminLastSeen::class,
            ]);
            // Add CSRF exception for Telegram webhook
            $middleware->validateCsrfTokens(except: [
                'bible/webhook',
                'telegram2/webhook',  // Add this line
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
