<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Positions_product_store extends Model
{
    use HasFactory;

    public $table = 'positions_product_store';

    public $fillable = [
        'id_product_store',
        'position'
    ];

    public function product_store(){
        return $this->belongsTo('App\Models\Product_Store', 'id_product_store');
    }
}
