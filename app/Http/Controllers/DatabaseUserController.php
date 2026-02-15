<?php

namespace App\Http\Controllers;

use App\Models\DatabaseUser;
use App\Services\MariaDBService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class DatabaseUserController extends Controller
{
    protected $mariaDBService;

    public function __construct(MariaDBService $mariaDBService)
    {
        $this->mariaDBService = $mariaDBService;
    }

    public function index()
    {
        $users = DatabaseUser::with('databases')->paginate(20);
        return view('database-users.index', compact('users'));
    }

    public function create()
    {
        return view('database-users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:32|unique:database_users,username',
            'host' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Générer un mot de passe sécurisé
        $password = $this->mariaDBService->generateSecurePassword();

        // Créer l'utilisateur dans MariaDB
        if (!$this->mariaDBService->createUser($validated['username'], $password, $validated['host'])) {
            return back()->withErrors(['error' => 'Failed to create MySQL user'])->withInput();
        }

        // Enregistrer dans la base de données de l'app
        $user = DatabaseUser::create([
            'username' => $validated['username'],
            'host' => $validated['host'],
            'encrypted_password' => Crypt::encryptString($password),
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('database-users.show', $user)
            ->with('success', 'Database user created successfully!')
            ->with('password', $password); // Afficher le mot de passe une seule fois
    }

    public function show(DatabaseUser $databaseUser)
    {
        $databaseUser->load('databases');
        return view('database-users.show', compact('databaseUser'));
    }

    public function edit(DatabaseUser $databaseUser)
    {
        return view('database-users.edit', compact('databaseUser'));
    }

    public function update(Request $request, DatabaseUser $databaseUser)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $databaseUser->update($validated);

        return redirect()->route('database-users.show', $databaseUser)
            ->with('success', 'Database user updated successfully!');
    }

    public function destroy(DatabaseUser $databaseUser)
    {
        // Supprimer de MariaDB
        $this->mariaDBService->deleteUser($databaseUser->username, $databaseUser->host);

        // Supprimer de la base
        $databaseUser->delete();

        return redirect()->route('database-users.index')
            ->with('success', 'Database user deleted successfully!');
    }
}
