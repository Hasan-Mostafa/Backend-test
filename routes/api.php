<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SecretaryController;
use App\Http\Middleware\DoctorMiddleware; 
use App\Http\Middleware\SecretaryMiddleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthenticationController::class,'register']);
Route::get('login', [AuthenticationController::class,'login']);
Route::get('logout', [AuthenticationController::class,'logout'])->middleware('auth:sanctum');

Route::get('/auth/callback', [GoogleController::class, 'OAuthGoogleCallback']);

// Doctor-specific API endpoints
Route::middleware(['auth:sanctum', DoctorMiddleware::class])->prefix('doctor')->group(function () {

    Route::get('/auth/redirect', [GoogleController::class, 'OAuthGoogle']);

    Route::post('appointments', [AppointmentController::class,'store']);
    Route::put('appointments/{id}', [AppointmentController::class,'update']);
    Route::delete('appointments/{id}', [AppointmentController::class,'destroy']);
    Route::get('appointments', [AppointmentController::class,'index']);

    Route::post('add_secretary', [SecretaryController::class,'addSecretary']);


});

// Secretary-specific API endpoints
Route::middleware(['auth:sanctum', SecretaryMiddleware::class])->prefix('secretary')->group(function () {

    Route::get('appointments', [SecretaryController::class,'viewDoctorAppointments']);

});

