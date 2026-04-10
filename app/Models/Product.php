<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_produk', 'category_id', 'item_code',
        'brand_produk', 'nama_produk', 'print_description', 'is_internal_only',
    ];

    protected $casts = ['is_internal_only' => 'boolean'];

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
}
