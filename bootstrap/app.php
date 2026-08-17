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
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Producción corre detrás de Traefik (Coolify), que termina el TLS y
        // reenvía por HTTP plano al contenedor. Sin esto, Laravel no confía
        // en los headers X-Forwarded-* y puede detectar un scheme/host
        // distinto entre el request que genera una URL firmada (ej. subida
        // de archivos de Livewire) y el que la valida -- la firma HMAC
        // incluye esa URL, así que el mismatch la invalida (401).
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
