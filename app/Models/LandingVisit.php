<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingVisit extends Model
{
    protected $table = 'landing_visits';

    protected $fillable = ['pagina', 'ip', 'user_agent', 'referrer'];
}
