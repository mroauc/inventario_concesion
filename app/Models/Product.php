<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Product
 * @package App\Models
 * @version April 8, 2023, 1:58 am UTC
 *
 * @property string $name
 * @property string $description
 * @property string $code
 * @property integer $stock
 */
class Product extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'products';
    

    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'description',
        'code',
        // 'stock',
        'id_category',
        'id_concession'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'code' => 'string',
        // 'stock' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required'
    ];

    public function warehouses(){
        return $this->belongsToMany('App\Models\Store','product_stores', 'id_store', 'id_product')->withPivot('id', 'stock', 'position')->using('App\Models\Product_Store');
    }
    
}
