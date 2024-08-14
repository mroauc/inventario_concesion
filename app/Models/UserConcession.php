<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserConcession extends Pivot
{
    use HasFactory;
    public $table = 'user_concession';

    public $fillable = [
        'id_user',
        'id_concession'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'id_user');
    }

    public function concession(){
        return $this->belongsTo('App\Models\Concession', 'id_concession');
    }
}
