<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // simple admin check; assumes User model has `is_admin` boolean attribute
        if (! Auth::check() || ! Auth::user()->is_admin) {
            // you could redirect or abort
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
