<?php

namespace App\Http\Controllers;

use App\Models\ManagedDatabase;
use App\Models\DatabaseUser;
use App\Services\MariaDBService;
use Illuminate\Http\Request;

class ManagedDatabaseController extends Controller
{
    protected $mariaDBService;

    public function __construct(MariaDBService $mariaDBService)
    {
        $this->mariaDBService = $mariaDBService;
    }

    public function index()
    {
        $databases = ManagedDatabase::with('users')->paginate(20);
        return view('databases.index', compact('databases'));
    }

    public function create()
    {
        return view('databases.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'database_name' => 'required|string|max:64|unique:managed_databases,database_name',
            'charset' => 'required|string|max:32',
            'collation' => 'required|string|max:64',
            'description' => 'nullable|string',
        ]);

        // Créer la base de données dans MariaDB
        if (!$this->mariaDBService->createDatabase(
            $validated['database_name'],
            $validated['charset'],
            $validated['collation']
        )) {
            return back()->withErrors(['error' => 'Failed to create database'])->withInput();
        }

        // Enregistrer dans la base de données de l'app
        $database = ManagedDatabase::create($validated);

        return redirect()->route('databases.show', $database)
            ->with('success', 'Database created successfully!');
    }

    public function show(ManagedDatabase $database)
    {
        $database->load('users');
        $availableUsers = DatabaseUser::where('is_active', true)->get();
        return view('databases.show', compact('database', 'availableUsers'));
    }

    public function edit(ManagedDatabase $database)
    {
        return view('databases.edit', compact('database'));
    }

    public function update(Request $request, ManagedDatabase $database)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $database->update($validated);

        return redirect()->route('databases.show', $database)
            ->with('success', 'Database updated successfully!');
    }

    public function destroy(ManagedDatabase $database)
    {
        // Supprimer de MariaDB
        $this->mariaDBService->deleteDatabase($database->database_name);

        // Supprimer de la base
        $database->delete();

        return redirect()->route('databases.index')
            ->with('success', 'Database deleted successfully!');
    }
}
