<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StudentController extends Controller
{
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
}
