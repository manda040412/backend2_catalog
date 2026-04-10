<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchCar extends Model
{
    protected $primaryKey = 'id_match';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id_match', 'product_id', 'car_brand', 'car_type',
        'car_chassis', 'engine_desc', 'car_body', 'year_from', 'year_to',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_produk');
    }
}
