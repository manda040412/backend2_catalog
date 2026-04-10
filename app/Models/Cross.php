<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cross extends Model
{
    protected $primaryKey = 'id_cross';

    protected $fillable = [
        'product_id', 'cross_brand', 'cross_item_code',
        'cross_nama_produk', 'oem_number',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_produk');
    }
}
