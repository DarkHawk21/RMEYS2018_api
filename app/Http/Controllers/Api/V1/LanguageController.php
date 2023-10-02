<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Language;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LanguageController extends Controller
{
    public function getTotalRegisters()
    {
        $totalRegisters = Language::count();

        return response()->json($totalRegisters);
    }
}
