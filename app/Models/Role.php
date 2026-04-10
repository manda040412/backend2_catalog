<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $primaryKey = 'id_role';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id_role', 'id_role_code', 'role_name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id_role');
    }
}
