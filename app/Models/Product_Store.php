<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Product_Store extends Pivot
{
    use HasFactory;

    public $table = 'product_stores';

    public $fillable = [
        'id_product',
        'id_store',
        'id_responsible',
        'stock'
    ];

    public function product(){
        return $this->belongsTo('App\Models\Product', 'id_product');
    }

    public function store(){
        return $this->belongsTo('App\Models\Store', 'id_store');
    }

    public function responsible(){
        return $this->belongsTo('App\Models\User', 'id_responsible');
    }

    public function positions(){  
        return $this->hasMany('App\Models\Positions_product_store', 'id_product_store');
    }
}
