<?php

namespace App\Support;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

final class AdminActivityLogger
{
    /**
     * @param array<string,mixed> $metadata
     */
    public static function log(
        User $admin,
        Request $request,
        string $eventType,
        string $module,
        string $description,
        ?string $routeName = null,
        ?string $httpMethod = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        array $metadata = []
    ): void {
        AdminActivityLog::query()->create([
            'admin_id' => $admin->id,
            'event_type' => $eventType,
            'module' => $module,
            'route_name' => $routeName,
            'http_method' => $httpMethod,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'description' => $description,
            'metadata' => $metadata === [] ? null : $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }
}

