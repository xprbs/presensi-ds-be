<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LBSController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InfoController;
use App\Http\Controllers\API\AbsenceController;
use App\Http\Controllers\API\PresenceInController;
use App\Http\Controllers\API\PresenceOutController;
use App\Http\Controllers\API\StudentDataController;
use App\Http\Controllers\API\OnlinePresenceController;
use App\Http\Controllers\API\HistoryPresenceController;

Route::post('login', [AuthController::class, 'login']);
Route::post('reset-password', [AuthController::class, 'requestResetPassword']);

Route::middleware('apiAuth')->group(function () {
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::get('info/student', [InfoController::class, 'student']);

    // Route::get('my-presence', [AbsenceController::class, 'store']);
    Route::post('absence', [AbsenceController::class, 'store']);
    
    Route::get('lbs/check', [LBSController::class, 'checkPerimeter']);

    Route::prefix('presence-in')->controller(PresenceInController::class)->group(function() {
        Route::get('check', 'check');
        Route::post('offline', 'storeOffline');
        Route::post('online', 'storeOnline');
    });

    Route::prefix('presence-out')->controller(PresenceOutController::class)->group(function() {
        Route::get('check', 'check');
        Route::post('offline', 'storeOffline');
        Route::post('online', 'storeOnline');

    });

    // Route::post('presence-out', [PresenceOutController::class, 'store']);
    // Route::get('presence-out', [PresenceOutController::class, 'check']);

    Route::get('presence/history', [HistoryPresenceController::class, 'index']);

    Route::prefix('teacher')->group(function() {
        Route::get('info', [InfoController::class, 'teacher']);
        Route::get('presence/today', [HistoryPresenceController::class, 'today']);
        Route::get('presence', [HistoryPresenceController::class, 'presenceOnDate']);
        Route::get('presence/semester', [HistoryPresenceController::class, 'semester']);
        Route::get('students', [StudentDataController::class, 'index']);
    });
});
