<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('database_users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('host')->default('localhost');
            $table->text('encrypted_password');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['username', 'host']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('database_users');
    }
};
