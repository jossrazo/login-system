<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Manages CRUD operations for user records.
 * All database interactions use PDO prepared statements explicitly
 * to prevent SQL injection, as required by the coding test.
 */
class RecordController extends Controller
{
    private function pdo(): \PDO
    {
        return DB::connection()->getPdo();
    }

    /**
     * Display a paginated list of records belonging to the authenticated user.
     */
    public function index(Request $request): View
    {
        $userId = auth()->id();
        $search = $request->get('search', '');

        $pdo = $this->pdo();

        if ($search) {
            $stmt = $pdo->prepare(
                'SELECT * FROM records
                 WHERE user_id = :user_id
                   AND (first_name LIKE :search OR last_name LIKE :search OR email LIKE :search OR department LIKE :search)
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
     * Store a new record using a PDO prepared statement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'max:255', 'unique:records,email'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'department' => ['nullable', 'string', 'max:100'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $pdo  = $this->pdo();
        $stmt = $pdo->prepare(
            'INSERT INTO records (user_id, first_name, last_name, email, phone, department, notes, created_at, updated_at)
             VALUES (:user_id, :first_name, :last_name, :email, :phone, :department, :notes, NOW(), NOW())'
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

        return redirect()->route('records.index')
            ->with('success', 'Record created successfully.');
    }

    /**
     * Show the form to edit an existing record.
     * Scopes query to the authenticated user to prevent unauthorized access.
     */
    public function edit(int $id): View
    {
        $pdo  = $this->pdo();
        $stmt = $pdo->prepare(
            'SELECT * FROM records WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([':id' => $id, ':user_id' => auth()->id()]);
        $record = $stmt->fetch(\PDO::FETCH_OBJ);

        abort_if(! $record, 404);

        return view('records.edit', compact('record'));
    }

    /**
     * Update an existing record using a PDO prepared statement.
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

        $pdo  = $this->pdo();

        // Verify ownership before updating
        $check = $pdo->prepare('SELECT id FROM records WHERE id = :id AND user_id = :user_id LIMIT 1');
        $check->execute([':id' => $id, ':user_id' => auth()->id()]);
        abort_if(! $check->fetch(), 404);

        $stmt = $pdo->prepare(
            'UPDATE records
             SET first_name = :first_name, last_name = :last_name, email = :email,
                 phone = :phone, department = :department, notes = :notes, updated_at = NOW()
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

        return redirect()->route('records.index')
            ->with('success', 'Record updated successfully.');
    }

    /**
     * Delete a record — ownership check prevents deleting other users' records.
     */
    public function destroy(int $id): RedirectResponse
    {
        $pdo  = $this->pdo();
        $stmt = $pdo->prepare(
            'DELETE FROM records WHERE id = :id AND user_id = :user_id'
        );
        $stmt->execute([':id' => $id, ':user_id' => auth()->id()]);

        return redirect()->route('records.index')
            ->with('success', 'Record deleted successfully.');
    }
}
