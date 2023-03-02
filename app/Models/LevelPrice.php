<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LevelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'level_name',
        'role_id',
    ];

    protected $appends = [
        'role_name',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function roles()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function getRoleNameAttribute()
    {
        $role = Role::find($this->role_id);

        return $role ? $role->role_name : '-';
    }
}
