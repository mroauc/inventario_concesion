<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Store
 * @package App\Models
 * @version April 8, 2023, 3:43 pm UTC
 *
 * @property string $name
 * @property string $address
 */
class Store extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'stores';
    

    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'address',
        'id_concession'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'address' => 'string'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required'
    ];

    public function products(){
        return $this->belongsToMany('App\Models\Product','product_stores', 'id_store', 'id_product')->withPivot('id', 'stock')->using('App\Models\Product_Store');
    }

    
}
