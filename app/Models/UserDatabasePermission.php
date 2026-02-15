<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDatabasePermission extends Model
{
    protected $fillable = [
        'database_user_id',
        'managed_database_id',
        'privileges'
    ];

    protected $casts = [
        'privileges' => 'array',
    ];

    public function databaseUser(): BelongsTo
    {
        return $this->belongsTo(DatabaseUser::class);
    }

    public function managedDatabase(): BelongsTo
    {
        return $this->belongsTo(ManagedDatabase::class);
    }
}
