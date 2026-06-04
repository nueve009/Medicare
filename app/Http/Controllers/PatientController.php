<?php

namespace App\Http\Controllers;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // 1. READ ALL (GET /api/patients)
    public function index(Request $request)
    {
        $patients = Patient::with('creator:id,first_name,last_name')
            ->where('clinic_id', $request->header('X-Clinic-ID'))
            ->where('created_by', $request->user()->id)  // ← add this
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
            'gender'       => 'nullable|string|in:male,female,other',
            'birthdate'    => 'required|date',
            'email'        => 'nullable|email|unique:patients,email',
            'phone_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'blood_type'   => 'nullable|string|max:5',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $validatedData['created_by'] = $request->user()->id;

        $patient = Patient::create($validatedData);

        return response()->json([
            'message' => 'Patient created successfully',
            'patient' => $patient,
        ], 201);
    }

    // 3. READ ONE (GET /api/patients/{id})
        public function show(Request $request, Patient $patient)
    {
        if ($patient->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json(
            $patient->load('creator:id,first_name,last_name')
        );
    }

    public function update(Request $request, Patient $patient)
    {
        if ($patient->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validatedData = $request->validate([
            'first_name'   => 'sometimes|string|max:255',
            'last_name'    => 'sometimes|string|max:255',
            'gender'       => 'nullable|string|in:male,female,other',
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

    public function destroy(Request $request, Patient $patient)
    {
        if ($patient->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient archived successfully',
        ]);
    }
}