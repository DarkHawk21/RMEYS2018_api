<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advisory extends Model
{
    protected $fillable = [
        'student_id',
        'schedule_event_id',
        'selected_date',
        'selected_time_start',
        'selected_time_end',
        'real_date_start',
        'real_date_end',
        'real_time_start',
        'real_time_end',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }
}
