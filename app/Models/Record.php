<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a user-created profile record in the CRUD dashboard.
 *
 * Each record belongs to exactly one user (via user_id foreign key).
 * The database enforces this with ON DELETE CASCADE, so when a user
 * account is deleted, all their records are automatically removed too.
 *
 * Note: The model is defined here for Eloquent relationships and the
 * fullName accessor, but all actual DB reads/writes in RecordController
 * use explicit PDO prepared statements (as required by the coding test).
 */
class Record extends Model
{
    /**
     * Columns that may be mass-assigned.
     * All other columns are protected against mass-assignment by default.
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'department',
        'notes',
    ];

    /**
     * A record belongs to a single user.
     * This relationship enables $record->user to retrieve the owner.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor: combine first and last name into a single "Full Name" string.
     * Accessed via $record->full_name (snake_case of the method name without 'get'/'Attribute').
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
