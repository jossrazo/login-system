<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Handles new user registration.
 *
 * Security notes:
 * - Passwords are hashed with bcrypt via Hash::make() before being stored —
 *   equivalent to PHP's password_hash() with PASSWORD_BCRYPT.
 * - The email uniqueness check prevents duplicate accounts.
 * - Laravel's Password::defaults() enforces a minimum length and complexity rule.
 */
class RegisteredUserController extends Controller
{
    /**
     * Show the registration form (GET /register).
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Process the registration form submission (POST /register).
     *
     * Steps:
     * 1. Validate all inputs server-side.
     * 2. Hash the plain-text password with bcrypt before storing it —
     *    raw passwords are NEVER saved to the database.
     * 3. Fire the Registered event (used for email verification if enabled).
     * 4. Log the user in immediately and start a new session.
     * 5. Redirect to the dashboard.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Server-side validation: name required, email must be unique, password must be confirmed
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Hash the password with bcrypt (password_hash equivalent) before persisting
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Trigger the Registered event — can be used for sending verification emails
        event(new Registered($user));

        // Log the newly registered user in, creating an authenticated session
        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
