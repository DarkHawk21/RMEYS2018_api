<?php

namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdvisorSchedule;
use App\Http\Controllers\Controller;

class AdvisorScheduleController extends Controller
{
    public function getOne($scheduleId)
    {
        $advisorSchedule = AdvisorSchedule::with([
                'recurrence',
                'advisor.userDetail.language'
            ])
            ->findOrFail($scheduleId);

        $extendedProps = [
            'timeStart' => [
                'hours' => (int) Carbon::parse($advisorSchedule->start_date.'T'.$advisorSchedule->start_time)->format('h'),
                'minutes' => 0
            ],
            'timeEnd' => [
                'hours' => (int) Carbon::parse($advisorSchedule->end_date.'T'.$advisorSchedule->end_time)->format('h'),
                'minutes' => 0
            ],
            'advisor' => [
                'id' => $advisorSchedule->advisor->id,
                'language' => $advisorSchedule->advisor->userDetail->language->name,
                'img' => '',
                'name' => $advisorSchedule->advisor->userDetail->name
            ]
        ];

        $recurrence = [
            "startAt" => $advisorSchedule->recurrence->dtstart,
            "repeatTimes" => [
                "times" => $advisorSchedule->recurrence->interval,
                "type" => $advisorSchedule->recurrence->freq,
            ],
            "repeatDays" => json_decode($advisorSchedule->recurrence->byweekday),
            "finishAt" => [
                "type" => $advisorSchedule->recurrence->until
                    ? 'date'
                    : ($advisorSchedule->recurrence->count
                        ? 'times'
                        : 'never'),
                "value" => $advisorSchedule->recurrence->until
                    ? $advisorSchedule->recurrence->until
                    : ($advisorSchedule->recurrence->count
                        ? $advisorSchedule->recurrence->count
                        : 1)
            ],
            "exdate" => json_decode($advisorSchedule->recurrence->exdate)
        ];

        $advisorScheduleFinal = [
            'id' => $advisorSchedule->id,
            'groupId' => $advisorSchedule->groupId,
            'title' => $advisorSchedule->title,
            'backgroundColor' => $advisorSchedule->advisor->userDetail->language->bg_color,
            'borderColor' => $advisorSchedule->advisor->userDetail->language->bg_color,
            'textColor' => $advisorSchedule->advisor->userDetail->language->tx_color,
            'extendedProps' => $extendedProps
        ];

        if ($advisorSchedule->is_recurring) {
            $advisorScheduleFinal['recurrence'] = $recurrence;
            $advisorScheduleFinal['extendedProps']['recurrenceType'] = $advisorSchedule->recurrence->freq;
        } else {
            $start = $advisorSchedule->start_date.'T'.$advisorSchedule->start_time;
            $end = $advisorSchedule->end_date.'T'.$advisorSchedule->end_time;

            $advisorScheduleFinal['start'] = $start;
            $advisorScheduleFinal['end'] = $end;
        }

        return response()->json($advisorScheduleFinal);
    }

    public function getAdvisorSchedule($advisorId)
    {
        $advisor = User::findOrFail($advisorId);

        $advisorSchedule = AdvisorSchedule::with([
                'recurrence',
                'advisor.userDetail.language'
            ])
            ->where('user_id', $advisor->id)
            ->get()
            ->map(function($row) {
                $extendedProps = [
                    'timeStart' => [
                        'hours' => (int) Carbon::parse($row->start_date.'T'.$row->start_time)->format('h'),
                        'minutes' => 0
                    ],
                    'timeEnd' => [
                        'hours' => (int) Carbon::parse($row->end_date.'T'.$row->end_time)->format('h'),
                        'minutes' => 0
                    ],
                    'advisor' => [
                        'id' => $row->advisor->id,
                        'language' => $row->advisor->userDetail->language->name,
                        'img' => '',
                        'name' => $row->advisor->userDetail->name
                    ],
                    'recurrenceType' => ''
                ];

                $recurrence = [
                    "startAt" => $row->recurrence->dtstart,
                    "repeatTimes" => [
                        "times" => $row->recurrence->interval,
                        "type" => $row->recurrence->freq,
                    ],
                    "repeatDays" => json_decode($row->recurrence->byweekday),
                    "finishAt" => [
                        "type" => $row->recurrence->until,
                        "value" => $row->recurrence->until
                    ],
                    "exdate" => $row->recurrence->exdate
                ];

                $event = [
                    'id' => $row->id,
                    'groupId' => $row->groupId,
                    'title' => $row->title,
                    'backgroundColor' => $row->advisor->userDetail->language->bg_color,
                    'borderColor' => $row->advisor->userDetail->language->bg_color,
                    'textColor' => $row->advisor->userDetail->language->tx_color,
                    'extendedProps' => $extendedProps
                ];

                if ($row->is_recurring) {
                    $event['recurrence'] = $recurrence;
                    $event['duration'] = Carbon::parse($row->start_date.'T'.$row->start_time)->format('H:i');

                    switch($row->recurrence->freq) {
                        case 'weekly':
                        case 'monthly':
                        case 'yearly':
                            $event['rrule'] = [
                                "freq" => $row->recurrence->freq,
                                "dtstart" => $row->recurrence->dtstart,
                                "until" => $row->recurrence->until
                            ];
                            break;
                            break;
                        case 'daily':
                            break;
                        case 'personalized':
                            break;
                    }
                } else {
                    $start = $row->start_date.'T'.$row->start_time;
                    $end = $row->end_date.'T'.$row->end_time;

                    $event['start'] = $start;
                    $event['end'] = $end;
                }

                return $event;
            });

        return response()->json($advisorSchedule);
    }
}
