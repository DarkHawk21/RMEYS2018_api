<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisorSchedule extends Model
{
    protected $fillable = [
        'groupId',
        'user_id',
        'title',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'is_recurring'
    ];

    public function advisor()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function recurrence()
    {
        return $this->hasOne(
            AdvisorScheduleRecurrence::class,
            'advisor_schedule_id',
            'id'
        );
    }

    public function advisories()
    {
        return $this->hasMany(Advisory::class, 'schedule_event_id', 'id');
    }
}
