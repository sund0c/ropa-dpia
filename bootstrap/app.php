<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\EnforceSessionWindow::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Token CSRF tidak cocok (419): sesi sudah diakhiri dari tab lain atau kedaluwarsa
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() === 419) {
                return redirect()->route('beranda')->with('sesi_berakhir', true);
            }
        });
    })->create();
