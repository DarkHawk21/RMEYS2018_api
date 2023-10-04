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
            ],
            'recurrenceType' => ''
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

        if (!$advisorSchedule->is_recurring) {
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

                $event = [
                    'id' => $row->id,
                    'groupId' => $row->groupId,
                    'title' => $row->title,
                    'backgroundColor' => $row->advisor->userDetail->language->bg_color,
                    'borderColor' => $row->advisor->userDetail->language->bg_color,
                    'textColor' => $row->advisor->userDetail->language->tx_color,
                    'extendedProps' => $extendedProps
                ];

                if (!$row->is_recurring) {
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
