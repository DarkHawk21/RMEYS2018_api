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
            $selectedDate = $request->input('event.date');
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
}
