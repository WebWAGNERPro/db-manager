<?php

namespace App\Http\Controllers;

use App\Models\UserDatabasePermission;
use App\Models\DatabaseUser;
use App\Models\ManagedDatabase;
use App\Services\MariaDBService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $mariaDBService;

    public function __construct(MariaDBService $mariaDBService)
    {
        $this->mariaDBService = $mariaDBService;
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'database_user_id' => 'required|exists:database_users,id',
            'managed_database_id' => 'required|exists:managed_databases,id',
            'privileges' => 'required|array',
            'privileges.*' => 'in:SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER,INDEX,REFERENCES,ALL PRIVILEGES',
        ]);

        $user = DatabaseUser::findOrFail($validated['database_user_id']);
        $database = ManagedDatabase::findOrFail($validated['managed_database_id']);

        // Accorder les privilèges dans MariaDB
        if (!$this->mariaDBService->grantPrivileges(
            $user->username,
            $user->host,
            $database->database_name,
            $validated['privileges']
        )) {
            return back()->withErrors(['error' => 'Failed to grant privileges']);
        }

        // Enregistrer dans la base de données de l'app
        UserDatabasePermission::updateOrCreate(
            [
                'database_user_id' => $validated['database_user_id'],
                'managed_database_id' => $validated['managed_database_id'],
            ],
            [
                'privileges' => $validated['privileges'],
            ]
        );

        return back()->with('success', 'Privileges granted successfully!');
    }

    public function revoke(UserDatabasePermission $permission)
    {
        $user = $permission->databaseUser;
        $database = $permission->managedDatabase;

        // Révoquer dans MariaDB
        $this->mariaDBService->revokePrivileges(
            $user->username,
            $user->host,
            $database->database_name
        );

        // Supprimer de la base
        $permission->delete();

        return back()->with('success', 'Privileges revoked successfully!');
    }
}
