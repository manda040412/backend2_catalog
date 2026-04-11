<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchCar extends Model
{
    protected $primaryKey = 'id_match';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id_match',
        'product_id',
        'item_code',
        'car_maker',   // FIX: nama kolom sesuai DB
        'car_model',   // FIX: nama kolom sesuai DB
        'year',        // FIX: string "2008 - 2018"
        'engine_desc',
        'chassis_code',
        'car_body',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_produk');
    }
}