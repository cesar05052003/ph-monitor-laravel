<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicion extends Model
{
    protected $table = 'mediciones';

    protected $fillable = [
        'valor_ph',
        'tipo_superficie',
        'fecha',
        'hora',
    ];
}
