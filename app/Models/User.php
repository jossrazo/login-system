<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Represents a registered user in the system.
 *
 * Key security attributes:
 * - password: stored as a bcrypt hash (never plain text). Marked as 'hashed'
 *   in casts so Laravel automatically hashes it when assigning.
 * - remember_token: excluded from JSON/array output via the #[Hidden] attribute.
 * - role: either 'user' (default) or 'admin'. Controls access to the admin panel.
 *
 * The #[Fillable] attribute whitelists which columns can be mass-assigned,
 * preventing mass-assignment vulnerabilities on unintended columns.
 */
#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Attribute casts:
     * - email_verified_at: cast to Carbon datetime for easy comparison.
     * - password: cast as 'hashed' so Laravel automatically runs
     *   password_hash() (bcrypt) whenever the password attribute is set.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Check whether this user has the admin role.
     *
     * Usage: $user->isAdmin()
     * Used in Blade views (@if(Auth::user()->isAdmin())) and
     * in AdminController to guard role-toggle actions.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
