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
            $records = $request->input('records', []);

            foreach ($records as $record) {
                switch(Str::slug($record['idioma'])) {
                    case 'ingles':
                        $bgColor = '#cf142b';
                        $txColor = '#fff';
                        break;
                    case 'frances':
                        $bgColor = '#002b7f';
                        $txColor = '#fff';
                        break;
                    case 'aleman':
                        $bgColor = '#ffce00';
                        $txColor = '#000';
                        break;
                    case 'italiano':
                        $bgColor = '#009846';
                        $txColor = '#fff';
                        break;
                    case 'portugues':
                        $bgColor = '#fff';
                        $txColor = '#000';
                        break;
                }

                Language::updateOrCreate(
                    [
                        'code' => Str::slug($record['idioma'])
                    ],
                    [
                        'id' => $record['id'],
                        'code' => Str::slug($record['idioma']),
                        'name' => $record['idioma'],
                        'bg_color' => $bgColor,
                        'tx_color' => $txColor
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
