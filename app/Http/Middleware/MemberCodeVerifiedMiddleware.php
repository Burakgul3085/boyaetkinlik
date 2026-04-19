<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MemberCodeVerifiedMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('member_code_verified', false)) {
            return redirect()->route('member.login.verify.form');
        }

        return $next($request);
    }
}
