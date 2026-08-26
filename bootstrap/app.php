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
        //FH-53 El endpoint del servidor MCP lo llaman clientes externos sin sesion de navegador,
        //asi que no pueden mandar token CSRF. Se autentica por el token secreto en la URL en su lugar.
        $middleware->validateCsrfTokens(except: ['mcp/*']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
