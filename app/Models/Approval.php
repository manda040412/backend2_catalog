<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $primaryKey = 'id_approval';

    protected $fillable = ['user_id', 'approved_by', 'status', 'notes', 'approved_at'];

    protected $casts = ['approved_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id_user');
    }
}
