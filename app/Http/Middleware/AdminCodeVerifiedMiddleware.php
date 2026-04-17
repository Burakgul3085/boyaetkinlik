<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCodeVerifiedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('admin_code_verified', false)) {
            return redirect()->route('admin.verify.form');
        }

        return $next($request);
    }
}
