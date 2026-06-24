<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\QueueController; 
use App\Http\Controllers\UserController;
use App\Http\Controllers\PrescriptionPdfController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// -------------------------------------------------------
// Public Routes
// -------------------------------------------------------
Route::post('/auth/login', [AuthController::class, 'login']);

// -------------------------------------------------------
// Protected Routes (Require valid Sanctum token)
// -------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Verify token validity on app boot
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Admin-only: Clinics & Users
    Route::apiResource('clinics', ClinicController::class);
    Route::get('users', [UserController::class, 'index']);
    Route::put('users/{user}', [UserController::class, 'update']);

    // Reference Data: Generics, Brands, Diseases
    Route::apiResource('generics', GenericController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('diseases', DiseaseController::class);
    Route::get('diseases/{disease}/patients', [DiseaseController::class, 'patients']);
    Route::patch('diseases/{disease}/diagnoses/{diagnosis}', [DiseaseController::class, 'updateDiagnosisStatus']);

    Route::get(
    'consultations/{consultation}/prescription-pdf/signed-url',
    [PrescriptionPdfController::class, 'generateSignedUrl']
    )->middleware('auth:sanctum');

    // -------------------------------------------------------
    // Clinic-scoped Routes
    // -------------------------------------------------------
    Route::middleware('clinic.access')->group(function () {
        Route::apiResource('patients', PatientController::class);
        Route::get('patients/{patient}/diagnoses', [PatientController::class, 'diagnoses']);

        Route::apiResource('consultations', ConsultationController::class);

        // Fixed: Added 'store' to prescriptions
        Route::apiResource('prescriptions', PrescriptionController::class)->only([
            'store',
            'update',
            'destroy',
        ]);

        // Queue routes
        Route::get('queue', [QueueController::class, 'index']);
        Route::post('queue', [QueueController::class, 'store']);
        Route::delete('queue/{queueEntry}', [QueueController::class, 'destroy']);
        Route::delete('queue/by-patient/{patientId}', [QueueController::class, 'destroyByPatient']);
    });
});