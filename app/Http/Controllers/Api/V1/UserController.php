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
    public function getOne($advisorId)
    {
        $roleAdvisor = UserRole::where('code', 'advisor')->first();

        $advisor = User::with(['userDetail.language'])
            ->where('id', $advisorId)
            ->where('role_id', $roleAdvisor->id)
            ->first();

        $advisor = [
            "id" => $advisor->id,
            "name" => $advisor->userDetail->name,
            "language" => $advisor->userDetail->language->name,
            "img" => $advisor->userDetail->img
        ];

        return response()->json($advisor);
    }

    public function getAdvisors()
    {
        $roleAdvisor = UserRole::where('code', 'advisor')->first();

        $advisors = User::with(['userDetail'])
            ->where('role_id', $roleAdvisor->id)
            ->get();

        return response()->json($advisors);
    }

    public function getTotalRegisters(Request $request)
    {
        $isSocialService = $request->input('social_service', 0);

        $totalRegisters = User::whereHas('userDetail', function($query) use($isSocialService) {
                $query->where('social_service', $isSocialService);
            })
            ->count();

        return response()->json($totalRegisters);
    }

    public function syncRegisters(Request $request)
    {
        Log::debug("Request");
        Log::debug($request);
        DB::beginTransaction();

        try {
            $records = $request->input('records', []);
            Log::debug("Records");
            Log::debug($records);

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
                            'img' => '',
                            'language_id' => Language::where('name', $record['idioma'])
                                ->first()
                                ->id
                        ]
                    );

                    Log::debug("Se creó usuario:");
                    Log::debug($record);
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
