<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use App\Models\Language;
use Illuminate\Http\Request;
use Illuminate\Support\Str as Str;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function getTotalRegisters()
    {
        $totalRegisters = Language::count();

        return response()->json($totalRegisters);
    }

    public function syncRegisters(Request $request)
    {
        DB::beginTransaction();

        try {
            Language::truncate();

            $records = $request->input('records', []);

            foreach ($records as $record) {
                Language::create(
                    [
                        'id' => $record['id'],
                        'code' => Str::slug($record['idioma']),
                        'name' => $record['idioma']
                    ]
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
