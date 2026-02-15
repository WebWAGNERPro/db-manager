<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MariaDBService
{
    /**
     * Créer un utilisateur MariaDB
     */
    public function createUser(string $username, string $password, string $host = 'localhost'): bool
    {
        try {
            // Échapper le mot de passe
            $escapedPassword = addslashes($password);
            
            DB::statement("CREATE USER '{$username}'@'{$host}' IDENTIFIED BY '{$escapedPassword}'");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to create MySQL user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer un utilisateur MariaDB
     */
    public function deleteUser(string $username, string $host = 'localhost'): bool
    {
        try {
            DB::statement("DROP USER IF EXISTS '{$username}'@'{$host}'");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to delete MySQL user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Créer une base de données
     */
    public function createDatabase(string $databaseName, string $charset = 'utf8mb4', string $collation = 'utf8mb4_unicode_ci'): bool
    {
        try {
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to create database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprimer une base de données
     */
    public function deleteDatabase(string $databaseName): bool
    {
        try {
            DB::statement("DROP DATABASE IF EXISTS `{$databaseName}`");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to delete database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Accorder des privilèges
     */
    public function grantPrivileges(string $username, string $host, string $databaseName, array $privileges): bool
    {
        try {
            $privs = implode(', ', $privileges);
            DB::statement("GRANT {$privs} ON `{$databaseName}`.* TO '{$username}'@'{$host}'");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to grant privileges: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Révoquer tous les privilèges
     */
    public function revokePrivileges(string $username, string $host, string $databaseName): bool
    {
        try {
            DB::statement("REVOKE ALL PRIVILEGES ON `{$databaseName}`.* FROM '{$username}'@'{$host}'");
            return true;
        } catch (\Exception $e) {
            logger()->error('Failed to revoke privileges: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Générer un mot de passe sécurisé
     */
    public function generateSecurePassword(int $length = 32): string
    {
        return Str::random($length);
    }
}
