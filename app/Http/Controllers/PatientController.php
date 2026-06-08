<?php

namespace App\Http\Controllers;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    // 1. READ ALL (GET /api/patients)
    public function index(Request $request)
    {
        $clinicId = $request->header('X-Clinic-ID');
        $user = $request->user();

        $query = Patient::where('clinic_id', '=', $clinicId);

        // Doctors only see their own patients
        // Assistants see all patients in the shared clinic
        if ($user->role === 'doctor') {
            $query->where('created_by', '=', $user->id);
        }

        $patients = $query->orderBy('last_name', 'asc')->paginate(20);

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
            'clinic_id'    => 'required|exists:clinics,id',
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
        $this->authorizePatientAccess($request, $patient);

        return response()->json(
            $patient->load('creator:id,first_name,last_name')
        );
    }

    // 4. UPDATE (PUT /api/patients/{id})
    public function update(Request $request, Patient $patient)
    {
        $this->authorizePatientAccess($request, $patient);

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

    // 5. DELETE (DELETE /api/patients/{id})
    public function destroy(Request $request, Patient $patient)
    {
        // Assistants cannot delete patients
        if ($request->user()->role === 'assistant') {
            return response()->json([
                'message' => 'Unauthorized. Assistants cannot delete patient records.',
            ], 403);
        }

        // Doctors can only delete their own patients
        if ($patient->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient archived successfully',
        ]);
    }

    // — Shared access check —
    // Doctors: own patients only
    // Assistants: any patient in the shared clinic
    private function authorizePatientAccess(Request $request, Patient $patient): void
    {
        $user = $request->user();

        if ($user->role === 'assistant') {
            // Assistant must share a clinic with the patient
            $clinicId = $request->header('X-Clinic-ID');
            if ((string) $patient->clinic_id !== (string) $clinicId) {
                abort(403, 'This patient does not belong to your active clinic.');
            }
        } else {
            // Doctor must own the patient
            if ($patient->created_by !== $user->id) {
                abort(403, 'Forbidden');
            }
        }
    }
}