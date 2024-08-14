<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    public $table = 'logs';

    public $fillable = [
        'activity',
        'content',
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
