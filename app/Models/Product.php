<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_produk';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id_produk', 'category_id', 'item_code', 'brand_produk',
        'nama_produk', 'print_description', 'description', 'is_internal_only',
    ];

    protected $casts = [
        'is_internal_only' => 'boolean',
        'deleted_at'       => 'datetime',
    ];

    public static function generateId(): string
    {
        $last = static::withTrashed()->orderByDesc('id_produk')->value('id_produk');
        if (!$last) return 'PROD-001';
        if (preg_match('/PROD-(\d+)$/', $last, $m)) {
            return 'PROD-' . str_pad(intval($m[1]) + 1, 3, '0', STR_PAD_LEFT);
        }
        return 'PROD-' . str_pad(static::withTrashed()->count() + 1, 3, '0', STR_PAD_LEFT);
    }

    public function category()     { return $this->belongsTo(Category::class, 'category_id', 'id_category'); }
    public function crosses()      { return $this->hasMany(Cross::class, 'product_id', 'id_produk'); }
    public function matchCars()    { return $this->hasMany(MatchCar::class, 'product_id', 'id_produk'); }

    public function scopePublicOnly(Builder $query): Builder
    {
        return $query->where('is_internal_only', 0);
    }
    public function scopeSearchByItemCode(Builder $query, string $keyword): Builder
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('item_code', 'like', "%{$keyword}%")
              ->orWhere('nama_produk', 'like', "%{$keyword}%");
        });
    }
    public function scopeSearchByOem(Builder $query, string $oem): Builder
    {
        return $query->whereHas('crosses', fn($q) => $q->where('oem_number', 'like', "%{$oem}%"));
    }
}