<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuth
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
        // Check if the admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu');
        }

        return $next($request);
    }
}