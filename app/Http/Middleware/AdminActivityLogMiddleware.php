<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\AdminActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AdminActivityLogMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $admin = $request->user();
            if (! $admin instanceof User || ! $admin->is_admin) {
                return $response;
            }

            $route = $request->route();
            $routeName = (string) ($route?->getName() ?? '');

            if ($routeName === '' || str_starts_with($routeName, 'admin.logs.')) {
                return $response;
            }

            $eventType = $this->resolveEventType($request->method(), $routeName);
            $module = $this->resolveModule($routeName);
            [$subjectType, $subjectId] = $this->resolveSubject($request);

            AdminActivityLogger::log(
                $admin,
                $request,
                $eventType,
                $module,
                $this->buildDescription($routeName, $eventType),
                $routeName,
                $request->method(),
                $subjectType,
                $subjectId,
                $this->buildMetadata($request)
            );
        } catch (Throwable $e) {
            report($e);
        }

        return $response;
    }

    private function resolveEventType(string $httpMethod, string $routeName): string
    {
        $routeName = strtolower($routeName);

        if (str_contains($routeName, '.approve')) {
            return 'approve';
        }
        if (str_contains($routeName, '.reject')) {
            return 'reject';
        }

        return match (strtoupper($httpMethod)) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'view',
        };
    }

    private function resolveModule(string $routeName): string
    {
        $parts = explode('.', $routeName);
        if (count($parts) >= 2) {
            return (string) $parts[1];
        }

        return 'general';
    }

    /**
     * @return array{0:?string,1:?int}
     */
    private function resolveSubject(Request $request): array
    {
        $route = $request->route();
        if (! $route) {
            return [null, null];
        }

        foreach ($route->parameters() as $parameter) {
            if (is_object($parameter) && method_exists($parameter, 'getKey')) {
                /** @var mixed $key */
                $key = $parameter->getKey();
                $subjectId = is_numeric($key) ? (int) $key : null;
                return [get_class($parameter), $subjectId];
            }

            if (is_scalar($parameter) && is_numeric($parameter)) {
                return [null, (int) $parameter];
            }
        }

        return [null, null];
    }

    /**
     * @return array<string,mixed>
     */
    private function buildMetadata(Request $request): array
    {
        $payload = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'register_password',
            'register_password_confirmation',
            'verification_code',
        ]);

        if ($payload === []) {
            return [];
        }

        return ['payload' => $payload];
    }

    private function buildDescription(string $routeName, string $eventType): string
    {
        $eventLabel = match ($eventType) {
            'create' => 'Ekleme',
            'update' => 'Güncelleme',
            'delete' => 'Silme',
            'approve' => 'Onaylama',
            'reject' => 'Reddetme',
            'view' => 'Görüntüleme',
            'login' => 'Giriş',
            'logout' => 'Çıkış',
            default => 'İşlem',
        };

        return "Rota: {$routeName} | İşlem: {$eventLabel}";
    }
}

