<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\AdvisorSchedule;
use App\Http\Controllers\Controller;
use App\Models\AdvisorScheduleRecurrence;

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
                        'minutes' => (int) Carbon::parse($row->start_date.'T'.$row->start_time)->format('i')
                    ],
                    'timeEnd' => [
                        'hours' => (int) Carbon::parse($row->end_date.'T'.$row->end_time)->format('h'),
                        'minutes' => (int) Carbon::parse($row->end_date.'T'.$row->end_time)->format('i')
                    ],
                    'advisor' => [
                        'id' => $row->advisor->id,
                        'language' => $row->advisor->userDetail->language->name,
                        'img' => '',
                        'name' => $row->advisor->userDetail->name
                    ]
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

                $isRecurring = $row->is_recurring;

                if (!$isRecurring) {
                    $start = $row->start_date.'T'.$row->start_time;
                    $end = $row->end_date.'T'.$row->end_time;
                    $event['start'] = $start;
                    $event['end'] = $end;
                } else {
                    $event['duration'] = $row->recurrence->duration;
                    $recurrenceType = $row->recurrence->recurrence_type;
                    $event['extendedProps']['recurrenceType'] = $recurrenceType;

                    $event['extendedProps']['recurrence'] = [
                        "startAt" => $row->recurrence->dtstart,
                        "repeatTimes" => [
                            "type" => $row->recurrence->freq,
                            "times" => $row->recurrence->interval,
                        ],
                        "repeatDays" => json_decode($row->recurrence->byweekday),
                        "finishAt" => [
                            "type" => $row->recurrence->until
                                ? 'date'
                                : 'never',
                            "value" => $row->recurrence->until
                        ]
                    ];

                    if ($recurrenceType === 'personalized') {
                        $event['rrule'] = [
                            "freq" => $row->recurrence->freq,
                            "dtstart" => $row->recurrence->dtstart,
                            "until" => $row->recurrence->until,
                            "interval" => $row->recurrence->interval,
                            "byweekday" => json_decode($row->recurrence->byweekday),
                        ];
                    } else if ($recurrenceType === 'daily') {
                        $event['rrule'] = [
                            "freq" => $row->recurrence->freq,
                            "dtstart" => $row->recurrence->dtstart,
                            "byweekday" => json_decode($row->recurrence->byweekday)
                        ];
                    } else {
                        $event['rrule'] = [
                            "freq" => $row->recurrence->freq,
                            "dtstart" => $row->recurrence->dtstart,
                        ];
                    }
                }

                return $event;
            });

        return response()->json($advisorSchedule);
    }

    public function storeOne(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = $request->input('extendedProps.advisor.id');
            $title = $request->input('title');
            $date = $request->input('date');
            $timeStart = $request->input('extendedProps.timeStart');
            $timeEnd = $request->input('extendedProps.timeEnd');
            $recurrenceType = $request->input('extendedProps.recurrenceType');
            $isRecurring = $recurrenceType !== 'never';

            $newAdvisorSchedule = AdvisorSchedule::create(
                [
                    'groupId' => Carbon::parse($date)->format('YmdHis'),
                    'user_id' => $userId,
                    'title' => $title,
                    'start_date' => Carbon::parse($date)->format('Y-m-d'),
                    'end_date' => Carbon::parse($date)->format('Y-m-d'),
                    'start_time' => Carbon::parse($timeStart['hours'].':'.$timeStart['minutes'].':00')->format('H:i:s'),
                    'end_time' => Carbon::parse($timeEnd['hours'].':'.$timeEnd['minutes'].':00')->format('H:i:s'),
                    'is_recurring' => $isRecurring,
                ]
            );

            if ($isRecurring) {
                $until = NULL;
                $interval = NULL;
                $byweekday = NULL;
                $freq = $recurrenceType;
                $duration = $timeEnd['hours'] - $timeStart['hours'];

                if ($recurrenceType === 'daily') {
                    $byweekday = json_encode(['mo', 'tu', 'we', 'th', 'fr']);
                } else if ($recurrenceType === 'personalized') {
                    $freq = $request->input('extendedProps.recurrence.repeatTimes.type');
                    $interval = $request->input('extendedProps.recurrence.repeatTimes.times');
                    $byweekday = json_encode($request->input('extendedProps.recurrence.repeatDays'));

                    if ($request->input('extendedProps.recurrence.finishAt.type') === 'date') {
                        $until = Carbon::parse($request->input('extendedProps.recurrence.finishAt.value'))->format('Y-m-d').' '.Carbon::parse($timeEnd['hours'].':'.$timeEnd['minutes'].':00')->format('H:i:s');
                    }
                }

                AdvisorScheduleRecurrence::create(
                    [
                        'advisor_schedule_id' => $newAdvisorSchedule->id,
                        'recurrence_type' => $recurrenceType,
                        'exdate' => $request->input('extendedProps.recurrence.exdate'),
                        'freq' => $freq,
                        'dtstart' => Carbon::parse($date)->format('Y-m-d').' '.Carbon::parse($timeStart['hours'].':'.$timeStart['minutes'].':00')->format('H:i:s'),
                        'duration' => $duration.':00:00',
                        'byweekday' => $byweekday,
                        'interval' => $interval,
                        'until' => $until
                    ]
                );
            }

            DB::commit();

            return response()->json([
                "error" => NULL,
                "message" => 'Registro creado correctamente.'
            ]);
        } catch(\Exception $e) {
            Log::info($e);

            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => 'Ocurrió un error al intentar crear el registro.'
            ], 500);
        }
    }
}
