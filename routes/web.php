<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DatabaseUserController;
use App\Http\Controllers\DatabaseExplorerController;
use App\Http\Controllers\ManagedDatabaseController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Gestion des utilisateurs BDD
    Route::resource('database-users', DatabaseUserController::class);

    // Gestion des bases de données
    Route::resource('databases', ManagedDatabaseController::class);

    // Gestion des permissions
    Route::post('permissions/assign', [PermissionController::class, 'assign'])->name('permissions.assign');
    Route::delete('permissions/{permission}', [PermissionController::class, 'revoke'])->name('permissions.revoke');

    // Explorateur de base de données (phpMyAdmin-like)
    Route::get('/explorer', [DatabaseExplorerController::class, 'home'])->name('explorer');
    Route::get('/explorer/api/{database}/{databaseUser}/tables', [DatabaseExplorerController::class, 'apiTables'])->name('explorer.api.tables');
    Route::get('/explorer/{database}/{databaseUser}/{table}', [DatabaseExplorerController::class, 'table'])->name('explorer.table');
    Route::post('/explorer/{database}/{databaseUser}/{table}', [DatabaseExplorerController::class, 'storeRow'])->name('explorer.store');
    Route::put('/explorer/{database}/{databaseUser}/{table}', [DatabaseExplorerController::class, 'updateRow'])->name('explorer.update');
    Route::delete('/explorer/{database}/{databaseUser}/{table}', [DatabaseExplorerController::class, 'deleteRow'])->name('explorer.delete');
    Route::delete('/explorer/{database}/{databaseUser}/{table}/bulk', [DatabaseExplorerController::class, 'bulkDeleteRows'])->name('explorer.bulk-delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
