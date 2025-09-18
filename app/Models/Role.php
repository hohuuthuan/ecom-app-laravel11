<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'description'];

    public function users()
    {
        // pivot đúng: role_user
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }
}
