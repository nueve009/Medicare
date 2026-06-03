<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\GenericController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PrescriptionController;
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

    // -------------------------------------------------------
    // Admin-only: Clinics
    // Authorization is enforced inside ClinicController
    // -------------------------------------------------------
    Route::apiResource('clinics', ClinicController::class);

    // -------------------------------------------------------
    // Reference Data: Generics, Brands, Diseases
    // No clinic scoping needed — these are global lookup tables
    // -------------------------------------------------------
    Route::apiResource('generics', GenericController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('diseases', DiseaseController::class);

    // -------------------------------------------------------
    // Clinic-scoped Routes
    // Requires X-Clinic-ID header. Middleware validates that
    // the authenticated user belongs to the requested clinic.
    // -------------------------------------------------------
    Route::middleware('clinic.access')->group(function () {
        Route::apiResource('patients', PatientController::class);

        Route::apiResource('consultations', ConsultationController::class);

        Route::apiResource('prescriptions', PrescriptionController::class)->only([
            'update',
            'destroy',
        ]);
    });
});