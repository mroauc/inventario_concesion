<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Representative extends Model
{
    use HasFactory;
    // use SoftDeletes;

    public $table = 'representative';
    
    

    // protected $dates = ['deleted_at'];

    public $fillable = [
        'name',
        'rut',
        'phone',
        'city',
        'address',
        'email'
    ];


}
