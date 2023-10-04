<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvisorSchedule extends Model
{
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
}
