<?php

namespace App\Http\Controllers;

use App\Models\DatabaseUser;
use App\Models\ManagedDatabase;
use App\Models\UserDatabasePermission;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DatabaseExplorerController extends Controller
{
    private function getConnection(DatabaseUser $databaseUser, ManagedDatabase $database)
    {
        $password = Crypt::decryptString($databaseUser->encrypted_password);
        $connectionName = 'explorer_' . $databaseUser->id . '_' . $database->id;

        config(["database.connections.{$connectionName}" => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', 3306),
            'database'  => $database->database_name,
            'username'  => $databaseUser->username,
            'password'  => $password,
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]]);

        DB::purge($connectionName);

        return DB::connection($connectionName);
    }

    private function verifyAccess(ManagedDatabase $database, DatabaseUser $databaseUser): void
    {
        $hasPermission = UserDatabasePermission::where('managed_database_id', $database->id)
            ->where('database_user_id', $databaseUser->id)
            ->exists();

        if (!$hasPermission) {
            abort(403, 'Cet utilisateur n\'a pas accès à cette base de données.');
        }
    }

    private function getSidebarData(): array
    {
        $permissions = UserDatabasePermission::with(['managedDatabase', 'databaseUser'])
            ->whereHas('managedDatabase', fn($q) => $q->where('is_active', true))
            ->whereHas('databaseUser', fn($q) => $q->where('is_active', true))
            ->get();

        return $permissions->groupBy('managed_database_id')->map(function ($perms) {
            $db = $perms->first()->managedDatabase;
            return [
                'id' => $db->id,
                'name' => $db->database_name,
                'users' => $perms->map(fn($p) => [
                    'id' => $p->databaseUser->id,
                    'username' => $p->databaseUser->username,
                    'host' => $p->databaseUser->host,
                ])->unique('id')->values()->toArray(),
            ];
        })->values()->toArray();
    }

    public function home()
    {
        $sidebarData = $this->getSidebarData();

        return view('explorer.home', compact('sidebarData'));
    }

    public function apiTables(ManagedDatabase $database, DatabaseUser $databaseUser)
    {
        $this->verifyAccess($database, $databaseUser);

        try {
            $conn = $this->getConnection($databaseUser, $database);
            $tables = $conn->select('SHOW TABLE STATUS');

            return response()->json(collect($tables)->map(function ($t) {
                $t = (array) $t;
                $sizeBytes = ($t['Data_length'] ?? 0) + ($t['Index_length'] ?? 0);
                return [
                    'name' => $t['Name'],
                    'rows' => $t['Rows'] ?? 0,
                    'engine' => $t['Engine'] ?? '-',
                    'size' => $sizeBytes >= 1048576
                        ? number_format($sizeBytes / 1048576, 2) . ' MB'
                        : number_format($sizeBytes / 1024, 1) . ' KB',
                ];
            }));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function table(ManagedDatabase $database, DatabaseUser $databaseUser, string $table)
    {
        $this->verifyAccess($database, $databaseUser);
        $sidebarData = $this->getSidebarData();

        try {
            $conn = $this->getConnection($databaseUser, $database);

            $validTables = collect($conn->select('SHOW TABLES'))
                ->map(fn($row) => array_values((array) $row)[0])
                ->toArray();

            if (!in_array($table, $validTables, true)) {
                abort(404, 'Table introuvable.');
            }

            $columns = $conn->select("SHOW FULL COLUMNS FROM `{$table}`");
            $indexes = $conn->select("SHOW INDEXES FROM `{$table}`");
            $total   = $conn->table($table)->count();

            $perPage  = 25;
            $page     = max(1, (int) request()->get('page', 1));
            $offset   = ($page - 1) * $perPage;
            $rows     = $conn->table($table)->limit($perPage)->offset($offset)->get();
            $lastPage = max(1, (int) ceil($total / $perPage));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur : ' . $e->getMessage()]);
        }

        return view('explorer.table', compact(
            'sidebarData', 'database', 'databaseUser', 'table',
            'columns', 'indexes', 'rows', 'total', 'page', 'perPage', 'lastPage'
        ));
    }

    private function getValidatedConnection(ManagedDatabase $database, DatabaseUser $databaseUser, string $table)
    {
        $this->verifyAccess($database, $databaseUser);
        $conn = $this->getConnection($databaseUser, $database);

        $validTables = collect($conn->select('SHOW TABLES'))
            ->map(fn($row) => array_values((array) $row)[0])
            ->toArray();

        if (!in_array($table, $validTables, true)) {
            abort(404, 'Table introuvable.');
        }

        return $conn;
    }

    private function getPrimaryKeys($conn, string $table): array
    {
        $columns = $conn->select("SHOW FULL COLUMNS FROM `{$table}`");
        return collect($columns)
            ->filter(fn($col) => ((array) $col)['Key'] === 'PRI')
            ->map(fn($col) => ((array) $col)['Field'])
            ->values()
            ->toArray();
    }

    public function storeRow(Request $request, ManagedDatabase $database, DatabaseUser $databaseUser, string $table)
    {
        try {
            $conn = $this->getValidatedConnection($database, $databaseUser, $table);
            $data = collect($request->input('fields', []))
                ->filter(fn($item) => isset($item['column']))
                ->mapWithKeys(fn($item) => [
                    $item['column'] => ($item['value'] === '' || !isset($item['value'])) ? null : $item['value']
                ])
                ->toArray();

            $conn->table($table)->insert($data);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur insertion : ' . $e->getMessage()]);
        }

        return redirect()
            ->route('explorer.table', [$database, $databaseUser, $table, 'tab' => 'browse', 'page' => $request->input('page', 1)])
            ->with('success', 'Ligne ajoutee avec succes.');
    }

    public function updateRow(Request $request, ManagedDatabase $database, DatabaseUser $databaseUser, string $table)
    {
        try {
            $conn = $this->getValidatedConnection($database, $databaseUser, $table);
            $primaryKeys = $this->getPrimaryKeys($conn, $table);
            $pkValues = $request->input('pk', []);
            $data = $request->input('data', []);

            $query = $conn->table($table);
            foreach ($primaryKeys as $pk) {
                if (!isset($pkValues[$pk])) {
                    return back()->withErrors(['error' => "Cle primaire manquante : {$pk}"]);
                }
                $query->where($pk, $pkValues[$pk]);
            }

            $query->update($data);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur mise a jour : ' . $e->getMessage()]);
        }

        return redirect()
            ->route('explorer.table', [$database, $databaseUser, $table, 'tab' => 'browse', 'page' => $request->input('page', 1)])
            ->with('success', 'Ligne modifiee avec succes.');
    }

    public function deleteRow(Request $request, ManagedDatabase $database, DatabaseUser $databaseUser, string $table)
    {
        try {
            $conn = $this->getValidatedConnection($database, $databaseUser, $table);
            $primaryKeys = $this->getPrimaryKeys($conn, $table);
            $pkValues = $request->input('pk', []);

            $query = $conn->table($table);
            foreach ($primaryKeys as $pk) {
                if (!isset($pkValues[$pk])) {
                    return back()->withErrors(['error' => "Cle primaire manquante : {$pk}"]);
                }
                $query->where($pk, $pkValues[$pk]);
            }

            $query->delete();
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur suppression : ' . $e->getMessage()]);
        }

        return redirect()
            ->route('explorer.table', [$database, $databaseUser, $table, 'tab' => 'browse', 'page' => $request->input('page', 1)])
            ->with('success', 'Ligne supprimee avec succes.');
    }
}
