<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'id',
        'code',
        'name',
        'bg_color',
        'tx_color'
    ];
}
