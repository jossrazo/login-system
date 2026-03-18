<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Manages CRUD operations for user records.
 *
 * All database interactions use PDO prepared statements explicitly
 * to prevent SQL injection, as required by the coding test.
 * Every PDO call is wrapped in try/catch so database errors are
 * logged server-side and the user sees a friendly message instead
 * of a raw stack trace.
 */
class RecordController extends Controller
{
    /**
     * Return the raw PDO connection for explicit prepared-statement usage.
     */
    private function pdo(): \PDO
    {
        return DB::connection()->getPdo();
    }

    /**
     * Display a list of records belonging to the authenticated user.
     * Supports optional keyword search across name, email and department.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();
        $search = $request->get('search', '');

        try {
            $pdo = $this->pdo();

            if ($search) {
                // Bind the search term once; the same named parameter is reused for each column
                $stmt = $pdo->prepare(
                    'SELECT * FROM records
                     WHERE user_id = :user_id
                       AND (first_name LIKE :search
                            OR last_name  LIKE :search
                            OR email      LIKE :search
                            OR department LIKE :search)
                     ORDER BY created_at DESC'
                );
                $stmt->execute([
                    ':user_id' => $userId,
                    ':search'  => "%{$search}%",
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'SELECT * FROM records WHERE user_id = :user_id ORDER BY created_at DESC'
                );
                $stmt->execute([':user_id' => $userId]);
            }

            $records = $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            Log::error('RecordController@index PDO error: ' . $e->getMessage());
            $records = [];
            session()->flash('error', 'Could not load records. Please try again later.');
        }

        return view('records.index', compact('records', 'search'));
    }

    /**
     * Show the form to create a new record.
     */
    public function create(): View
    {
        return view('records.create');
    }

    /**
     * Validate and persist a new record via a PDO prepared statement.
     * Passwords are never stored here; this table holds profile data only.
     */
    public function store(Request $request): RedirectResponse
    {
        // Server-side validation — mirrors the client-side rules in the Blade view
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:records,email'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $pdo  = $this->pdo();
            $stmt = $pdo->prepare(
                'INSERT INTO records
                    (user_id, first_name, last_name, email, phone, department, notes, created_at, updated_at)
                 VALUES
                    (:user_id, :first_name, :last_name, :email, :phone, :department, :notes, NOW(), NOW())'
            );

            $stmt->execute([
                ':user_id'    => auth()->id(),
                ':first_name' => $validated['first_name'],
                ':last_name'  => $validated['last_name'],
                ':email'      => $validated['email'],
                ':phone'      => $validated['phone'] ?? null,
                ':department' => $validated['department'] ?? null,
                ':notes'      => $validated['notes'] ?? null,
            ]);
        } catch (\PDOException $e) {
            Log::error('RecordController@store PDO error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Could not create the record. Please try again.');
        }

        return redirect()->route('records.index')
            ->with('success', 'Record created successfully.');
    }

    /**
     * Show the edit form for a record.
     * Scopes the query to the authenticated user so no one can edit another user's record.
     */
    public function edit(int $id): View
    {
        try {
            $pdo  = $this->pdo();
            $stmt = $pdo->prepare(
                'SELECT * FROM records WHERE id = :id AND user_id = :user_id LIMIT 1'
            );
            $stmt->execute([':id' => $id, ':user_id' => auth()->id()]);
            $record = $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $e) {
            Log::error('RecordController@edit PDO error: ' . $e->getMessage());
            abort(500, 'Could not load the record.');
        }

        abort_if(! $record, 404);

        return view('records.edit', compact('record'));
    }

    /**
     * Validate and apply updates to an existing record.
     * Ownership is verified before any UPDATE is executed.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', "unique:records,email,{$id}"],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $pdo = $this->pdo();

            // Verify the record belongs to the authenticated user before updating
            $check = $pdo->prepare(
                'SELECT id FROM records WHERE id = :id AND user_id = :user_id LIMIT 1'
            );
            $check->execute([':id' => $id, ':user_id' => auth()->id()]);
            abort_if(! $check->fetch(), 404);

            $stmt = $pdo->prepare(
                'UPDATE records
                 SET first_name  = :first_name,
                     last_name   = :last_name,
                     email       = :email,
                     phone       = :phone,
                     department  = :department,
                     notes       = :notes,
                     updated_at  = NOW()
                 WHERE id = :id AND user_id = :user_id'
            );

            $stmt->execute([
                ':first_name' => $validated['first_name'],
                ':last_name'  => $validated['last_name'],
                ':email'      => $validated['email'],
                ':phone'      => $validated['phone'] ?? null,
                ':department' => $validated['department'] ?? null,
                ':notes'      => $validated['notes'] ?? null,
                ':id'         => $id,
                ':user_id'    => auth()->id(),
            ]);
        } catch (\PDOException $e) {
            Log::error('RecordController@update PDO error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Could not update the record. Please try again.');
        }

        return redirect()->route('records.index')
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Delete a record.
     * The WHERE clause includes user_id so users can only delete their own records.
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $pdo  = $this->pdo();
            $stmt = $pdo->prepare(
                'DELETE FROM records WHERE id = :id AND user_id = :user_id'
            );
            $stmt->execute([':id' => $id, ':user_id' => auth()->id()]);
        } catch (\PDOException $e) {
            Log::error('RecordController@destroy PDO error: ' . $e->getMessage());

            return redirect()->route('records.index')
                ->with('error', 'Could not delete the record. Please try again.');
        }

        return redirect()->route('records.index')
            ->with('success', 'Record deleted successfully.');
    }
}
