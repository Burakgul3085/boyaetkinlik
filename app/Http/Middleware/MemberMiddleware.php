<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('member.login');
        }

        if ((bool) $request->user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
