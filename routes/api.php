<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WorkshopController;
use App\Http\Controllers\Api\V1\LanguageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function() {
    Route::group(['middleware' => ['api']], function() {
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('me', [AuthController::class, 'me'])->name('me');
    });

    Route::get('total-on-users', [UserController::class, 'getTotalRegisters'])->name('total-on-users');
    Route::get('total-on-workshops', [WorkshopController::class, 'getTotalRegisters'])->name('total-on-workshops');
    Route::get('total-on-languages', [LanguageController::class, 'getTotalRegisters'])->name('total-on-languages');
});
