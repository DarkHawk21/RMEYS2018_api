<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Advisory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdvisoryController extends Controller
{
    public function storeOne(Request $request)
    {
        DB::beginTransaction();

        try {
            $studentAccount = $request->input('studentAccount');
            $student = Student::where('ncuenta', $studentAccount)
                ->firstOrFail();
            $scheduleEventId = $request->input('event.id');
            $selectedDate = $request->input('event.start');
            $selectedTimeStart = $request->input('selectedHour.timeStart');
            $selectedTimeEnd = $request->input('selectedHour.timeEnd');

            Advisory::create(
                [
                    'student_id' => $student->id,
                    'schedule_event_id' => $scheduleEventId,
                    'selected_date' => Carbon::parse($selectedDate)->format('Y-m-d'),
                    'selected_time_start' => $selectedTimeStart['hours'].':'.$selectedTimeStart['minutes'].':00',
                    'selected_time_end' => $selectedTimeEnd['hours'].':'.$selectedTimeEnd['minutes'].':00',
                ]
            );

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

    public function getOneDisponibility($scheduleEventId, $selectedDate, $selectedTimeStart)
    {
        $maximumQuota = 5;

        $coincidentAdvisories = Advisory::where('schedule_event_id', $scheduleEventId)
            ->where('selected_date', $selectedDate)
            ->where('selected_time_start', $selectedTimeStart)
            ->count();

        $availableQuota = $maximumQuota - $coincidentAdvisories;

        return response()->json($availableQuota);
    }

    public function getOneDisponibilityByStudent($scheduleEventId, $selectedDate, $selectedTimeStart, $studentAccount)
    {
        $coincidentAdvisory = Advisory::where('schedule_event_id', $scheduleEventId)
            ->where('selected_date', $selectedDate)
            ->where('selected_time_start', $selectedTimeStart)
            ->whereHas('student', function($query) use($studentAccount) {
                $query->where('ncuenta', $studentAccount);
            })
            ->first();

        return response()->json($coincidentAdvisory);
    }

    public function getOneCheckInByStudent($scheduleEventId, $selectedDate, $selectedTimeStart, $studentAccount)
    {
        $studentCheckIn = Advisory::where('schedule_event_id', $scheduleEventId)
            ->where('selected_date', $selectedDate)
            ->where('selected_time_start', $selectedTimeStart)
            ->whereNotNull('real_date_start')
            ->whereNotNull('real_time_start')
            ->whereHas('student', function($query) use($studentAccount) {
                $query->where('ncuenta', $studentAccount);
            })
            ->first();

        return response()->json($studentCheckIn);
    }

    public function checkin(Request $request)
    {
        DB::beginTransaction();

        try {
            $studentAccount = $request->input('studentAccount');
            $scheduleEventId = $request->input('event.id');
            $selectedDate = $request->input('event.date');
            $selectedTimeStart = $request->input('selectedHour.timeStart');
            $checkInDate = $request->input('checkIn.date');
            $checkInTimeStart = $request->input('checkIn.timeStart');

            Advisory::where('schedule_event_id', $scheduleEventId)
                ->where('selected_date', Carbon::parse($selectedDate)->format('Y-m-d'))
                ->where('selected_time_start', $selectedTimeStart['hours'].':'.$selectedTimeStart['minutes'].':00')
                ->whereHas('student', function($query) use($studentAccount) {
                    $query->where('ncuenta', $studentAccount);
                })
                ->update(
                    [
                        'real_date_start' => $checkInDate,
                        'real_time_start' => $checkInTimeStart,
                    ]
                );

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
