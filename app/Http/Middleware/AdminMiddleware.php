<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    private const ADMIN_EMAIL = 'josephinenakalembe33@gmail.com';

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403);
        }

        return $next($request);
    }
}
