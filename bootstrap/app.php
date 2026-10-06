<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
->withExceptions(function (Exceptions $exceptions): void {
        // Intentionally empty: validation exceptions use Laravel's default handler,
        // which does NOT dump the request body (including the `messages` payload)
        // into logs. Do not add custom exception logging here.
    })->create();
