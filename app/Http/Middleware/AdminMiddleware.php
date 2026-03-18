<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-Based Access Control (RBAC) middleware.
 *
 * Guards any route that requires administrator privileges.
 * Registered as the 'admin' alias in bootstrap/app.php, so routes
 * can use ->middleware('admin') to protect them.
 *
 * Access logic:
 * - Must be authenticated (the 'auth' middleware runs first in the route group).
 * - The authenticated user's role column must equal 'admin'.
 * - Any other role (or unauthenticated request) receives a 403 Forbidden response.
 *
 * This ensures regular users cannot access /admin routes even if they
 * manually type the URL.
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Reject anyone who is not logged in or does not have the 'admin' role
        if (! auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // User is an admin — pass the request to the next handler in the pipeline
        return $next($request);
    }
}
