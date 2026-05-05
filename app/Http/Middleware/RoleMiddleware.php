<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        if (!in_array($userRole, $roles)) {
            // Instead of a blank 403 page, redirect to their own dashboard
            $fallback = match ($userRole) {
                'admin'    => route('admin.dashboard'),
                'hr'       => route('hr.dashboard'),
                'employee' => route('employee.dashboard'),
                default    => route('login'),
            };

            return redirect($fallback)
                ->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
