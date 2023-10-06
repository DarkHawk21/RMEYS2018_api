<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use App\Models\User;
use App\Models\Workshop;
use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkshopController extends Controller
{
    public function getWorkshopsByAdvisor($advisorId)
    {
        $workshopsByAdvisor = Workshop::where('user_id', $advisorId)
            ->get()
            ->map(function($workshop) {
                $workshop = [
                    "id" => $workshop->id,
                    "title" => $workshop->title,
                    "start" => $workshop->start,
                    "end" => $workshop->end,
                    "color" => 'gray',
                    "textColor" => 'white'
                ];

                return $workshop;
            });

        return response()->json($workshopsByAdvisor);
    }

    public function getTotalRegisters()
    {
        $totalRegisters = Workshop::count();

        return response()->json($totalRegisters);
    }

    public function syncRegisters(Request $request)
    {
        DB::beginTransaction();

        try {
            Workshop::truncate();

            $records = $request->input('records', []);

            foreach ($records as $record) {
                Workshop::create(
                    [
                        'id' => $record['id'],
                        'codTaller' => $record['codTaller'],
                        'title' => $record['title'],
                        'start' => $record['start'],
                        'end' => $record['end'],
                        'user_id' => User::whereHas('userDetail', function($query) use($record) {
                                $query->where('name', $record['asesor']);
                            })
                            ->first()
                            ->id,
                        'language_id' => Language::where(
                                'name',
                                $record['idioma']
                            )
                            ->first()
                            ->id,
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
