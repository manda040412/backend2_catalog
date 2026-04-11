<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    protected $primaryKey = 'id_produk';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id_produk',
        'category_id',
        'item_code',
        'brand_produk',
        'nama_produk',
        'print_description',
        'is_internal_only',
    ];

    protected $casts = [
        'is_internal_only' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────────
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id_category');
    }

    public function crosses()
    {
        return $this->hasMany(Cross::class, 'product_id', 'id_produk');
    }

    public function matchCars()
    {
        return $this->hasMany(MatchCar::class, 'product_id', 'id_produk');
    }

    // ─── Scopes ──────────────────────────────────────────────────
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
        return $query->whereHas('crosses', function ($q) use ($oem) {
            $q->where('oem_number', 'like', "%{$oem}%");
        });
    }
}
