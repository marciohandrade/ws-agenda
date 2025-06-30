<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ProtectSuperAdmin;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\RedirectAfterLogin;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrar middleware com alias
        $middleware->alias([
            'role' => CheckRole::class,
            'check.role' => CheckRole::class,
            'protect.super.admin' => ProtectSuperAdmin::class,
            'redirect.after.login' => RedirectAfterLogin::class,
        ]);
              
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();