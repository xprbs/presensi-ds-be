<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PresenceController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\PerimeterController;





Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'authenticate'])->name('postLogin');

Route::middleware('isAuth')->group(function () {
    Route::get('/', function () {
        return view('pages.index');
    })->name('index');
    Route::resource('class', ClassroomController::class);
    Route::resource('student', StudentController::class);
    Route::resource('perimeter', PerimeterController::class);
    Route::resource('teacher', TeacherController::class);
    Route::get('presence', [PresenceController::class, 'index'])->name('presence.index');
    Route::get('presence/get-student/{classroom}', [PresenceController::class, 'getStudent'])->name('presence.getStudent');
    Route::get('presence/student', [PresenceController::class, 'perStudent'])->name('presence.student');
    Route::get('reset-password/{token}', [AuthController::class, 'verifyToken'])->name('reset-password');
    Route::get('logout', [AuthController::class, 'logout'])->name('logout');

});


