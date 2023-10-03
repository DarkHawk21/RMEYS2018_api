<?php

namespace App\Http\Controllers\Api\V1;

use DB;
use Log;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Language;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function getTotalRegisters(Request $request)
    {
        $isAsesor = $request->input('social_service', 0);

        $totalRegisters = User::whereHas('userDetail', function($query) use($isAsesor) {
                $query->where('social_service', $isAsesor);
            })
            ->count();

        return response()->json($totalRegisters);
    }

    public function syncRegisters(Request $request)
    {
        DB::beginTransaction();

        try {
            $records = $request->input('records', []);

            foreach ($records as $record) {
                if (isset($record['correo']) && (isset($record['idasesor']) || (isset($record['id']) && $record['asesor'] == 1))) {
                    $newUser = User::updateOrCreate(
                        [
                            'email' => $record['correo']
                        ],
                        [
                            'email' => $record['correo'],
                            'password' => bcrypt($record['ncuenta']),
                            'role_id' => UserRole::where('code', 'advisor')
                                ->first()
                                ->id
                        ]
                    );

                    UserDetail::updateOrCreate(
                        [
                            'identifier' => $record['ncuenta']
                        ],
                        [
                            'user_id' => $newUser->id,
                            'external_id' => isset($record['id'])
                                ? $record['id']
                                : $record['idasesor'],
                            'identifier' => $record['ncuenta'],
                            'name' => $record['nombre'].' '.$record['appat'].' '.$record['apmat'],
                            'cellphone' => $record['telefono'],
                            'social_service' => isset($record['id'])
                                ? 1
                                : 0,
                            'language_id' => Language::where('name', $record['idioma'])
                                ->first()
                                ->id
                        ]
                    );
                }
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
