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

        $advisorSchedule = $this->formatEvent($advisorSchedule);

        return response()->json($advisorSchedule);
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
            ->map(function($event) {
                return $this->formatEvent($event);
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

                $exdateArray = $request->input('exdate');
                $exdateArrayFinal = [];

                foreach ($exdateArray as $exdate) {
                    $exdateArrayFinal[] = Carbon::parse($exdate)->format('Y-m-d').' '.Carbon::parse($exdate)->format('H:i:s');
                }

                AdvisorScheduleRecurrence::create(
                    [
                        'advisor_schedule_id' => $newAdvisorSchedule->id,
                        'recurrence_type' => $recurrenceType,
                        'exdate' => json_encode($exdateArrayFinal),
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

    public function formatEvent($eventToFormat)
    {
        $extendedProps = [
            'timeStart' => [
                'hours' => (int) Carbon::parse($eventToFormat->start_date.'T'.$eventToFormat->start_time)->format('h'),
                'minutes' => (int) Carbon::parse($eventToFormat->start_date.'T'.$eventToFormat->start_time)->format('i')
            ],
            'timeEnd' => [
                'hours' => (int) Carbon::parse($eventToFormat->end_date.'T'.$eventToFormat->end_time)->format('h'),
                'minutes' => (int) Carbon::parse($eventToFormat->end_date.'T'.$eventToFormat->end_time)->format('i')
            ],
            'advisor' => [
                'id' => $eventToFormat->advisor->id,
                'language' => $eventToFormat->advisor->userDetail->language->name,
                'img' => '',
                'name' => $eventToFormat->advisor->userDetail->name
            ]
        ];

        $event = [
            'id' => $eventToFormat->id,
            'groupId' => $eventToFormat->groupId,
            'title' => $eventToFormat->title,
            'backgroundColor' => $eventToFormat->advisor->userDetail->language->bg_color,
            'borderColor' => $eventToFormat->advisor->userDetail->language->bg_color,
            'textColor' => $eventToFormat->advisor->userDetail->language->tx_color,
            'extendedProps' => $extendedProps
        ];

        $isRecurring = $eventToFormat->is_recurring;

        if (!$isRecurring) {
            $event['extendedProps']['recurrenceType'] = 'never';
            $start = $eventToFormat->start_date.'T'.$eventToFormat->start_time;
            $end = $eventToFormat->end_date.'T'.$eventToFormat->end_time;
            $event['start'] = $start;
            $event['end'] = $end;
            $event['exdate'] = NULL;

            $event['extendedProps']['recurrence'] = [
                "startAt" => '',
                "repeatTimes" => [
                    "type" => 1,
                    "times" => "weekly",
                ],
                "repeatDays" => [],
                "finishAt" => [
                    "type" => 'never',
                    "value" => ''
                ]
            ];
        } else {
            $event['duration'] = $eventToFormat->recurrence->duration;
            $recurrenceType = $eventToFormat->recurrence->recurrence_type;
            $event['extendedProps']['recurrenceType'] = $recurrenceType;
            $event['exdate'] = json_decode($eventToFormat->recurrence->exdate);

            $event['extendedProps']['recurrence'] = [
                "startAt" => $eventToFormat->recurrence->dtstart,
                "repeatTimes" => [
                    "type" => $eventToFormat->recurrence->freq,
                    "times" => $eventToFormat->recurrence->interval,
                ],
                "repeatDays" => json_decode($eventToFormat->recurrence->byweekday),
                "finishAt" => [
                    "type" => $eventToFormat->recurrence->until
                        ? 'date'
                        : 'never',
                    "value" => $eventToFormat->recurrence->until
                ]
            ];

            if ($recurrenceType === 'personalized') {
                $event['rrule'] = [
                    "freq" => $eventToFormat->recurrence->freq,
                    "dtstart" => $eventToFormat->recurrence->dtstart,
                    "until" => $eventToFormat->recurrence->until,
                    "interval" => $eventToFormat->recurrence->interval,
                    "byweekday" => json_decode($eventToFormat->recurrence->byweekday),
                ];
            } else if ($recurrenceType === 'daily') {
                $event['rrule'] = [
                    "freq" => $eventToFormat->recurrence->freq,
                    "dtstart" => $eventToFormat->recurrence->dtstart,
                    "byweekday" => json_decode($eventToFormat->recurrence->byweekday)
                ];
            } else {
                $event['rrule'] = [
                    "freq" => $eventToFormat->recurrence->freq,
                    "dtstart" => $eventToFormat->recurrence->dtstart,
                ];
            }
        }

        return $event;
    }
}
