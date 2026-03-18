<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Manages the authenticated user's own profile settings.
 *
 * All routes here are protected by the 'auth' middleware,
 * so only logged-in users can reach these actions.
 *
 * Covered operations:
 * - View/update name and email
 * - Change password (handled separately by PasswordController)
 * - Delete the account permanently
 */
class ProfileController extends Controller
{
    /**
     * Show the profile edit page (GET /profile).
     * Passes the current user model to the view so forms can be pre-filled.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Save profile changes (PATCH /profile).
     *
     * ProfileUpdateRequest validates:
     * - name: required, string, max 255
     * - email: required, valid email, unique (excluding the current user's own email)
     *
     * If the email address changed, email_verified_at is cleared so the user
     * must re-verify their new address before accessing verified-only features.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Apply only the validated fields — prevents mass-assignment of arbitrary columns
        $request->user()->fill($request->validated());

        // If the email was changed, revoke the verification status
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Permanently delete the authenticated user's account (DELETE /profile).
     *
     * Requires the user to enter their current password as a confirmation step —
     * this prevents accidental or CSRF-triggered account deletion.
     *
     * After deletion:
     * 1. Log out the user.
     * 2. Delete the User record (cascades to associated records via FK).
     * 3. Invalidate the session and regenerate the CSRF token.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Re-confirm the password before performing this destructive action
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log out first so the session doesn't reference a deleted user
        Auth::logout();

        // Delete the user — the records table FK (ON DELETE CASCADE) removes their records too
        $user->delete();

        // Fully destroy the session to prevent any lingering authenticated state
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
