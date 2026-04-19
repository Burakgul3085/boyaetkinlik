<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminCodeVerifiedMiddleware;
use App\Http\Middleware\MemberCodeVerifiedMiddleware;
use App\Http\Middleware\MemberMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->validateCsrfTokens(except: [
            'shopier/callback',
        ]);
        $middleware->redirectGuestsTo(function ($request) {
            if ($request && $request->routeIs('admin.*')) {
                return route('admin.login');
            }

            return route('member.login');
        });

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'admin.code' => AdminCodeVerifiedMiddleware::class,
            'member' => MemberMiddleware::class,
            'member.code' => MemberCodeVerifiedMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
