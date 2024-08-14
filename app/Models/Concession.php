<?php

namespace App\Models;

use Eloquent as Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Class Concession
 * @package App\Models
 * @version April 7, 2023, 9:53 pm UTC
 *
 * @property string $name
 * @property string $address
 */
class Concession extends Model
{
    use SoftDeletes;

    use HasFactory;

    public $table = 'concessions';
    

    protected $dates = ['deleted_at'];



    public $fillable = [
        'name',
        'address',
        'id_representative'
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

    public function representative(){
        return $this->belongsTo('App\Models\User', 'id_representative');
    }

    public function users(){
        return $this->belongsToMany('App\Models\User','user_concession', 'id_user', 'id_concession')->using('App\Models\UserConcession');
    }
    
}
