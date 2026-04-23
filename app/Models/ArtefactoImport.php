<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtefactoImport extends Model
{
    protected $table = 'artefacto_imports';

    protected $fillable = [
        'id_user',
        'id_concession',
        'archivo',
        'total_rows',
        'success_count',
        'error_count',
        'errors',
    ];

    protected $casts = [
        'errors' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function concession()
    {
        return $this->belongsTo(Concession::class, 'id_concession');
    }
}
