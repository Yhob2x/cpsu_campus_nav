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
            return redirect('/login')->with('error', 'Please login to access admin area.');
        }
        
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access. Admin only area.');
        }
        
        return $next($request);
    }
}