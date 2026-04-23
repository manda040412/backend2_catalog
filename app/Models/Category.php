<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_category';
    public $incrementing  = false;
    protected $keyType    = 'string';

    protected $fillable = ['id_category', 'category_name', 'description'];

    protected $casts = ['deleted_at' => 'datetime'];

    public static function generateId(): string
    {
        $last = static::withTrashed()->orderByDesc('id_category')->value('id_category');
        if (!$last) return 'KAT-001';
        if (preg_match('/KAT-(\d+)$/', $last, $m)) {
            return 'KAT-' . str_pad(intval($m[1]) + 1, 3, '0', STR_PAD_LEFT);
        }
        return 'KAT-' . str_pad(static::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id_category');
    }
}