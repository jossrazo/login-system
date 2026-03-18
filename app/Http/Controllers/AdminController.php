<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Admin-only controller.
 * Protected by the AdminMiddleware — only users with role='admin' can access these routes.
 */
class AdminController extends Controller
{
    private function pdo(): \PDO
    {
        return DB::connection()->getPdo();
    }

    /**
     * Show all records from all users — admin view.
     */
    public function index(Request $request): View
    {
        $search = $request->get('search', '');

        try {
            $pdo = $this->pdo();

            if ($search) {
                $stmt = $pdo->prepare(
                    'SELECT r.*, u.name AS owner_name, u.email AS owner_email
                     FROM records r
                     JOIN users u ON u.id = r.user_id
                     WHERE r.first_name LIKE :search
                        OR r.last_name  LIKE :search
                        OR r.email      LIKE :search
                        OR r.department LIKE :search
                        OR u.name       LIKE :search
                     ORDER BY r.created_at DESC'
                );
                $stmt->execute([':search' => "%{$search}%"]);
            } else {
                $stmt = $pdo->prepare(
                    'SELECT r.*, u.name AS owner_name, u.email AS owner_email
                     FROM records r
                     JOIN users u ON u.id = r.user_id
                     ORDER BY r.created_at DESC'
                );
                $stmt->execute();
            }

            $records = $stmt->fetchAll(\PDO::FETCH_OBJ);

            // Also fetch all users for the admin overview panel
            $userStmt = $pdo->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
            $userStmt->execute();
            $users = $userStmt->fetchAll(\PDO::FETCH_OBJ);

        } catch (\PDOException $e) {
            Log::error('AdminController@index PDO error: ' . $e->getMessage());
            $records = [];
            $users   = [];
            session()->flash('error', 'Could not load admin data. Please try again.');
        }

        return view('admin.index', compact('records', 'users', 'search'));
    }

    /**
     * Promote a user to admin or demote them back to user.
     */
    public function toggleRole(int $userId): \Illuminate\Http\RedirectResponse
    {
        // Prevent admins from demoting themselves
        if ($userId === auth()->id()) {
            return redirect()->route('admin.index')
                ->with('error', 'You cannot change your own role.');
        }

        try {
            $pdo  = $this->pdo();
            $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id LIMIT 1');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(\PDO::FETCH_OBJ);

            abort_if(! $user, 404);

            $newRole  = $user->role === 'admin' ? 'user' : 'admin';
            $upd      = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $upd->execute([':role' => $newRole, ':id' => $userId]);

        } catch (\PDOException $e) {
            Log::error('AdminController@toggleRole PDO error: ' . $e->getMessage());

            return redirect()->route('admin.index')
                ->with('error', 'Could not update user role.');
        }

        return redirect()->route('admin.index')
            ->with('success', "User role updated to {$newRole}.");
    }
}
