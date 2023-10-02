<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Workshop;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WorkshopController extends Controller
{
    public function getTotalRegisters()
    {
        $totalRegisters = Workshop::count();

        return response()->json($totalRegisters);
    }
}
