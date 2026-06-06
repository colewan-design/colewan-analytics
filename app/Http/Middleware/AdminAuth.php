<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('analytics_admin')) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
