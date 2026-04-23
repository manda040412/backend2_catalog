<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    protected $primaryKey = 'id_user';

    protected $fillable = [
        'role_id', 'name', 'email', 'password',
        'company', 'phone', 'is_approved',
        'two_fa_code', 'two_fa_expires_at', 'two_fa_verified_at',
    ];

    protected $hidden = ['password', 'remember_token', 'two_fa_code'];

    protected $casts = [
        'two_fa_expires_at'  => 'datetime',
        'two_fa_verified_at' => 'datetime',
        'is_approved'        => 'boolean',
        'deleted_at'         => 'datetime',
    ];

    public function getAuthIdentifierName(): string { return 'id_user'; }
    public function getAuthIdentifier()             { return $this->id_user; }

    public function role()        { return $this->belongsTo(Role::class, 'role_id', 'id_role'); }
    public function approvals()   { return $this->hasMany(Approval::class, 'user_id', 'id_user'); }
    public function activityLogs(){ return $this->hasMany(ActivityLog::class, 'user_id', 'id_user'); }

    public function hasRole(string $roleCode): bool
    {
        return $this->role && $this->role->id_role_code === $roleCode;
    }

    public function isInternal(): bool
    {
        return in_array($this->role?->id_role_code, ['SADM', 'ADM', 'INT']);
    }
}