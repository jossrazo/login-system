<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates login form validation and authentication logic.
 *
 * This Form Request is responsible for:
 * - Validating the submitted email and password fields.
 * - Enforcing a rate limit of 5 failed attempts per email+IP combination
 *   to prevent brute-force attacks.
 * - Delegating password verification to Auth::attempt(), which internally
 *   calls password_verify() against the stored bcrypt hash.
 */
class LoginRequest extends FormRequest
{
    /**
     * All users may attempt to log in — no prior authorization needed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for the login form.
     * These run automatically before authenticate() is called.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the supplied credentials.
     *
     * Process:
     * 1. Check rate limit — throw if too many failed attempts.
     * 2. Call Auth::attempt() which uses password_verify() internally to
     *    compare the plain-text password against the stored bcrypt hash.
     * 3. On failure: increment the rate-limit counter and throw a
     *    ValidationException with a generic "These credentials do not match"
     *    message (deliberately vague to avoid user enumeration).
     * 4. On success: clear the rate-limit counter.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        // Block the request if it has exceeded 5 failed attempts
        $this->ensureIsNotRateLimited();

        // Auth::attempt() hashes the submitted password and compares it to the
        // stored hash using password_verify() — returns false if they don't match
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Increment failed-attempt counter (per email + IP)
            RateLimiter::hit($this->throttleKey());

            // Throw a generic error — do NOT reveal whether the email exists
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Authentication succeeded — reset the failed-attempt counter
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Throw a validation error if this email+IP has exceeded the attempt limit.
     * Fires a Lockout event which can be used to notify the user or log the incident.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // Allow up to 5 attempts before locking out
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Fire the Lockout event for logging / notification purposes
        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Build the rate-limit key from the lowercased email and client IP address.
     * Using both prevents one IP from locking out another user's account.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
