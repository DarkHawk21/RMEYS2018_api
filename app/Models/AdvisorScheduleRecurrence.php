<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisorScheduleRecurrence extends Model
{
    protected $fillable = [
        'advisor_schedule_id',
        'exdate',
        'recurrence_type',
        'duration',
        'freq',
        'dtstart',
        'interval',
        'until',
        'byweekday'
    ];
}
