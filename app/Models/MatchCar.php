<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchCar extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_match';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id_match', 'product_id', 'item_code',
        'car_maker', 'car_model', 'year',
        'engine_desc', 'chassis_code', 'car_body',
    ];

    protected $casts = ['deleted_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_produk');
    }
}