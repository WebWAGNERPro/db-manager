<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ManagedDatabase extends Model
{
    protected $fillable = [
        'database_name',
        'charset',
        'collation',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(DatabaseUser::class, 'user_database_permissions')
            ->withPivot('privileges')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(UserDatabasePermission::class);
    }
}
