<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Category_product
 * @package App\Models
 * @version April 8, 2023, 1:19 am UTC
 *
 * @property strign $name
 */
class Category_product extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'category_products';
    

    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'id_concession'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required'
    ];

    
}
