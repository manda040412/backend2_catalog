<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cross extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_cross';

    protected $fillable = [
        'product_id', 'cross_brand', 'cross_item_code',
        'cross_nama_produk', 'oem_number',
    ];

    protected $casts = ['deleted_at' => 'datetime'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id_produk');
    }
}