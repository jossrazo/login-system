<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Manages user login and logout (session lifecycle).
 *
 * Authentication flow:
 * - Login:  validate credentials → verify password via password_verify() →
 *           regenerate session ID (prevents session fixation) → redirect.
 * - Logout: invalidate the session → regenerate CSRF token → redirect to home.
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login form (GET /login).
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login attempt (POST /login).
     *
     * Delegates credential checking to LoginRequest::authenticate(), which:
     * - Enforces rate limiting (max 5 attempts per email+IP)
     * - Calls Auth::attempt() → internally uses password_verify() to compare
     *   the submitted password against the stored bcrypt hash
     *
     * After successful authentication:
     * - session()->regenerate() assigns a new session ID to prevent
     *   session fixation attacks.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Authenticate — throws ValidationException on failure or rate-limit breach
        $request->authenticate();

        // Regenerate the session ID after login to prevent session fixation
        $request->session()->regenerate();

        // Redirect to the originally intended URL (or dashboard as fallback)
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Log the user out (POST /logout).
     *
     * Three steps ensure the session is fully destroyed:
     * 1. logout()           — removes the authenticated user from the session
     * 2. invalidate()       — deletes all session data and regenerates the session ID
     * 3. regenerateToken()  — issues a new CSRF token so old forms can't reuse the old one
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Remove the authenticated user marker from the session
        Auth::guard('web')->logout();

        // Destroy all session data (including the session ID)
        $request->session()->invalidate();

        // Issue a fresh CSRF token so any cached forms become invalid
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
