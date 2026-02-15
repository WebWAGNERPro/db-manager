<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_database_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('database_user_id')->constrained()->onDelete('cascade');
            $table->foreignId('managed_database_id')->constrained()->onDelete('cascade');
            $table->json('privileges'); // SELECT, INSERT, UPDATE, DELETE, etc.
            $table->timestamps();
            
            // Nom de clé unique court
            $table->unique(['database_user_id', 'managed_database_id'], 'user_db_perms_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_database_permissions');
    }
};
