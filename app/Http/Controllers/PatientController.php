<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // 1. READ ALL (GET /api/patients)
    public function index()
    {
        $patients = Patient::with('creator:user_id,first_name,last_name')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($patients);
    }

    // 2. CREATE (POST /api/patients)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'gender'       => 'nullable|string|in:Male,Female,Other',
            'birthdate'    => 'required|date',
            'email'        => 'nullable|email|unique:patients,email',
            'phone_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'blood_type'   => 'nullable|string|max:5',
        ]);

        // Use user_id since that is the actual primary key on the users table
        $validatedData['created_by'] = $request->user()->user_id;

        $patient = Patient::create($validatedData);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient,
        ], 201);
    }

    // 3. READ ONE (GET /api/patients/{id})
    public function show(Patient $patient)
    {
        return response()->json(
            $patient->load('creator:user_id,first_name,last_name')
        );
    }

    // 4. UPDATE (PUT/PATCH /api/patients/{id})
    public function update(Request $request, Patient $patient)
    {
        $validatedData = $request->validate([
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'gender'       => 'nullable|string|in:Male,Female,Other',
            'birthdate'    => 'sometimes|date',
            'email'        => 'nullable|email|unique:patients,email,' . $patient->id,
            'phone_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'blood_type'   => 'nullable|string|max:5',
        ]);

        $patient->update($validatedData);

        return response()->json([
            'message' => 'Patient updated successfully',
            'patient' => $patient,
        ]);
    }

    // 5. DELETE (DELETE /api/patients/{id})
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return response()->json([
            'message' => 'Patient archived successfully',
        ]);
    }
}