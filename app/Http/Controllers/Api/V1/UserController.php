<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
