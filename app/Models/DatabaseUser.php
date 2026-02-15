<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DatabaseUser extends Model
{
    protected $fillable = [
        'username',
        'host',
        'encrypted_password',
        'notes',
        'is_active'
    ];

    protected $hidden = [
        'encrypted_password'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function databases(): BelongsToMany
    {
        return $this->belongsToMany(ManagedDatabase::class, 'user_database_permissions')
            ->withPivot('privileges')
            ->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(UserDatabasePermission::class);
    }
}
