<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    protected $fillable = [
        'id',
        'codTaller',
        'title',
        'start',
        'end',
        'user_id',
        'language_id',
    ];

    public $timestamps = false;
}
