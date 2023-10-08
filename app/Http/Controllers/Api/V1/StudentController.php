<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use Carbon\Carbon;
use App\Models\Student;
use App\Models\Advisory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
    public function getOne($studentAccount)
    {
        $student = Student::where('ncuenta', $studentAccount)
            ->first();

        return response()->json($student);
    }

    public function getTotalRegisters()
    {
        $totalRegisters = Student::count();

        return response()->json($totalRegisters);
    }

    public function syncRegisters(Request $request)
    {
        DB::beginTransaction();

        try {
            $records = $request->input('records', []);

            foreach ($records as $record) {
                Student::updateOrCreate(
                    [
                        'ncuenta' => $record['ncuenta']
                    ],
                    $record
                );
            }

            DB::commit();

            return response()->json([
                "error" => NULL,
                "message" => 'Tabla sincronizada correctamente.'
            ]);
        } catch(\Exception $e) {
            Log::info($e);

            DB::rollBack();

            return response()->json([
                "error" => $e->getMessage(),
                "message" => 'Ocurrió un error al intentar sincronizar la tabla.'
            ], 500);
        }
    }

    public function getLastCheckin($studentAccount)
    {
        $lastCheckIn = Advisory::with(['student', 'scheduleEvent.advisor.userDetail.language'])
            ->whereHas('student', function($query) use($studentAccount) {
                $query->where('ncuenta', $studentAccount);
            })
            ->whereDate('selected_date', Carbon::now('GMT-6')->format('Y-m-d'))
            ->whereTime('selected_time_start', '<=', Carbon::now('GMT-6')->format('H:i:s'))
            ->whereNull('real_time_end')
            ->first();

        return response()->json($lastCheckIn);
    }
}
