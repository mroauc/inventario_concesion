<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactoLanding extends Model
{
    protected $table = 'contactos_landing';
    protected $fillable = ['nombre', 'email', 'telefono', 'asunto', 'mensaje', 'leido'];
}
