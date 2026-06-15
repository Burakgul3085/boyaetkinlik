<?php

use App\Models\Setting;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AdminActivityLogMiddleware;
use App\Http\Middleware\AdminCodeVerifiedMiddleware;
use App\Http\Middleware\MemberCodeVerifiedMiddleware;
use App\Http\Middleware\MemberMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/__sistem-durumu', function () {
                $checks = [
                    'php' => PHP_VERSION,
                    'app_key' => config('app.key') ? 'ok' : 'EKSİK',
                    'app_env' => (string) config('app.env'),
                ];

                foreach (['storage/logs', 'storage/framework/views', 'storage/framework/sessions', 'bootstrap/cache'] as $dir) {
                    $path = base_path($dir);
                    $checks['writable_'.$dir] = is_dir($path) && is_writable($path);
                }

                $checks['route_cache'] = is_file(base_path('bootstrap/cache/routes-v7.php'));
                $checks['config_cache'] = is_file(base_path('bootstrap/cache/config.php'));
                $checks['services_cache'] = is_file(base_path('bootstrap/cache/services.php'));

                $routeCache = base_path('bootstrap/cache/routes-v7.php');
                if (is_file($routeCache)) {
                    $checks['route_cache_has_google'] = str_contains((string) file_get_contents($routeCache), 'MemberGoogleAuth');
                }

                try {
                    DB::connection()->getPdo();
                    $checks['db'] = 'ok';
                } catch (\Throwable $e) {
                    $checks['db'] = $e->getMessage();
                }

                try {
                    $checks['settings'] = Setting::getValue('header_site_name', 'Boya Etkinlik');
                } catch (\Throwable $e) {
                    $checks['settings'] = $e->getMessage();
                }

                try {
                    view('frontend.hakkimizda')->render();
                    $checks['view_render'] = 'ok';
                } catch (\Throwable $e) {
                    $checks['view_render'] = $e->getMessage();
                }

                return response()->json($checks, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            });
        },
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
            'admin.activity' => AdminActivityLogMiddleware::class,
            'member' => MemberMiddleware::class,
            'member.code' => MemberCodeVerifiedMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
