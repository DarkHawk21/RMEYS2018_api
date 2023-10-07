<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WorkshopController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\AdvisorScheduleController;
use App\Http\Controllers\Api\V1\AdvisoryController;
use App\Http\Controllers\Api\V1\StudentController;

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
    Route::group(['middleware' => ['cors']], function() {
        // Credentials
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::post('register', [AuthController::class, 'register'])->name('register');

        Route::group(['middleware' => ['auth:api']], function() {
            // Auth
            Route::post('logout', [AuthController::class, 'logout'])->name('logout');
            Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
            Route::post('me', [AuthController::class, 'me'])->name('me');

            // Resources
            Route::get('advisors', [UserController::class, 'getAdvisors'])->name('advisors');
            Route::get('advisors/schedule', [AdvisorScheduleController::class, 'getAdvisorsSchedule'])->name('advisors-schedule');
            Route::get('advisors/{advisorId}', [UserController::class, 'getOne'])->name('advisor');
            Route::get('advisors/{advisorId}/schedule', [AdvisorScheduleController::class, 'getAdvisorSchedule'])->name('advisor-schedule');
            Route::get('advisors/{advisorId}/workshops', [WorkshopController::class, 'getWorkshopsByAdvisor'])->name('workshops-by-advisor');

            Route::get('students/{studentAccount}', [StudentController::class, 'getOne'])->name('student');

            Route::get('workshops', [WorkshopController::class, 'getWorkshops'])->name('workshops');

            Route::post('advisories', [AdvisoryController::class, 'storeOne'])->name('store-one-advisory');

            Route::get('total-on-users', [UserController::class, 'getTotalRegisters'])->name('total-on-users');
            Route::get('total-on-students', [StudentController::class, 'getTotalRegisters'])->name('total-on-students');
            Route::get('total-on-workshops', [WorkshopController::class, 'getTotalRegisters'])->name('total-on-workshops');
            Route::get('total-on-languages', [LanguageController::class, 'getTotalRegisters'])->name('total-on-languages');

            Route::get('schedule/{scheduleId}', [AdvisorScheduleController::class, 'getOne'])->name('advisor-one-schedule');
            Route::post('schedule', [AdvisorScheduleController::class, 'storeOne'])->name('store-one-schedule');
            Route::put('schedule/{scheduleId}', [AdvisorScheduleController::class, 'updateOne'])->name('update-one-schedule');
            Route::delete('schedule/{scheduleId}', [AdvisorScheduleController::class, 'deleteOne'])->name('delete-one-schedule');

            // Sync
            Route::post('sync-users', [UserController::class, 'syncRegisters'])->name('sync-users');
            Route::post('sync-students', [StudentController::class, 'syncRegisters'])->name('sync-students');
            Route::post('sync-workshops', [WorkshopController::class, 'syncRegisters'])->name('sync-workshops');
            Route::post('sync-languages', [LanguageController::class, 'syncRegisters'])->name('sync-languages');
        });
    });
});
