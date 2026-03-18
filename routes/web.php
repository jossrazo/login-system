<?php

/**
 * web.php — Application web routes
 *
 * Route protection layers used here:
 * - 'auth'     → user must be logged in (session-based)
 * - 'verified' → user must have confirmed their email
 * - 'admin'    → user must have role='admin' (AdminMiddleware)
 *
 * CSRF protection is applied automatically by Laravel to all
 * POST / PUT / PATCH / DELETE routes in this file.
 */

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------
// Public route — accessible to everyone, logged in or not
// ---------------------------------------------------------------
Route::get('/', function () {
    return view('welcome');
});

// ---------------------------------------------------------------
// Dashboard — requires login AND verified email
// ---------------------------------------------------------------
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// ---------------------------------------------------------------
// Authenticated routes — all require a valid login session
// ---------------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Profile management (view, update name/email, delete account)
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Records CRUD dashboard
    // All DB operations in RecordController use explicit PDO prepared statements.
    // Routes: GET /records, GET /records/create, POST /records,
    //         GET /records/{id}/edit, PUT /records/{id}, DELETE /records/{id}
    Route::resource('records', RecordController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Admin-only routes — stacked middleware: auth (above) + admin (below)
    // Only users with role='admin' can access these; others get 403 Forbidden
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::post('/users/{user}/toggle-role', [AdminController::class, 'toggleRole'])->name('toggle-role');
    });
});

// Load authentication routes (login, register, password reset, etc.)
require __DIR__.'/auth.php';
