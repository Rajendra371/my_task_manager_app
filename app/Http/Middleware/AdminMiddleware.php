<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access admin dashboard.');
        }
        
        if (Auth::user()->role !== 'admin') {
            return redirect()->route('admin.login')->with('error', 'Admin access required.');
        }

        return $next($request);
    }
}