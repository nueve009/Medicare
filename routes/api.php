<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConsultationController;
use app\Http\Controllers\PatientController;
use App\Http\Controllers\PatientRecordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);

// Protected Routes (Require valid Sanctum Token)
Route::middleware('auth:sanctum')->group(function () {
    
    // Verify token validity on app boot
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Admin creates new users
    Route::post('/register', [AuthController::class, 'register']);

    Route::apiResource('patients', PatientController::class);
    Route::apiResource('patient-records', PatientRecordController::class);
    Route::apiResource('consultations', ConsultationController::class);

    // Logout and destroy token
    Route::post('/logout', [AuthController::class, 'logout']);
});