<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class LecturerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->role !== 'lecturer') {
            abort(403, 'Only lecturers can access this area.');
        }

        return $next($request);
    }
}
