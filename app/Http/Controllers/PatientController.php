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

        // Both doctors and assistants see all patients in the shared clinic
        $patients = Patient::where('clinic_id', '=', $clinicId)
            ->orderBy('last_name', 'asc')
            ->paginate(20);

        return response()->json($patients);
    }

    // 2. CREATE (POST /api/patients)
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'gender'         => 'nullable|string|in:male,female,other',
            'birthdate'      => 'required|date',
            'email'          => 'nullable|email|unique:patients,email',
            'phone_number'   => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'blood_type'     => 'nullable|string|max:5',
            'clinic_id'      => 'required|exists:clinics,id',
            'civil_status'   => 'nullable|in:single,married,divorced,separated,widowed,minor',
            'height'         => 'nullable|numeric|between:0,300',
            'weight'         => 'nullable|numeric|between:0,600',
            'temperature'    => 'nullable|numeric|between:30,45',
            'blood_pressure' => 'nullable|string|max:10',
            'allergies'      => 'nullable|string',
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
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'gender'         => 'nullable|string|in:male,female,other',
            'birthdate'      => 'sometimes|date',
            'email'          => 'nullable|email|unique:patients,email,' . $patient->id,
            'phone_number'   => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'blood_type'     => 'nullable|string|max:5',
            'civil_status'   => 'nullable|in:single,married,divorced,separated,widowed,minor',
            'height'         => 'nullable|numeric|between:0,300',
            'weight'         => 'nullable|numeric|between:0,600',
            'temperature'    => 'nullable|numeric|between:30,45',
            'blood_pressure' => 'nullable|string|max:10',
            'allergies'      => 'nullable|string',
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

        // Doctor must belong to the same clinic as the patient
        $clinicId = $request->header('X-Clinic-ID');
        if ((string) $patient->clinic_id !== (string) $clinicId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $patient->delete();

        return response()->json([
            'message' => 'Patient archived successfully',
        ]);
    }

    public function diagnoses(Request $request, Patient $patient)
    {
        // Enforce same access rules as show()
        $this->authorizePatientAccess($request, $patient);

        $diagnoses = \App\Models\ConsultationDisease::with([
                'consultation:id,consultation_date,chief_complaint,notes,clinic_id',
                'consultation.prescriptions.generic:id,generic_name',
                'consultation.prescriptions.brand:id,brand_name',
            ])
            ->where('disease_id', '!=', 0) // ensure valid rows only
            ->whereHas('consultation', function ($q) use ($patient, $request) {
                $q->where('patient_id', '=', $patient->id)
                ->where('clinic_id', '=', $request->header('X-Clinic-ID'));
            })
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($cd) {
                return [
                    'diagnosis_id'    => $cd->id,
                    'disease_id'      => $cd->disease_id,
                    'disease_name'    => $cd->disease_name_snapshot ?? 'Unknown',
                    'type'            => $cd->type,
                    'status'          => $cd->status,
                    'symptoms'        => $cd->symptoms,
                    'diagnosed_at'    => $cd->consultation->consultation_date,
                    'consultation_id' => $cd->consultation_id,
                    'chief_complaint' => $cd->consultation->chief_complaint,
                    'notes'           => $cd->consultation->notes,
                    'prescriptions'   => $cd->consultation->prescriptions->map(function ($rx) {
                        return [
                            'id'        => $rx->id,
                            'generic'   => $rx->generic?->generic_name ?? $rx->generic_name_snapshot ?? 'Unknown',
                            'brand'     => $rx->brand?->brand_name ?? $rx->brand_name_snapshot ?? 'Unknown',
                            'dosage'    => $rx->dosage,
                            'frequency' => $rx->frequency,
                            'duration'  => $rx->duration,
                        ];
                    }),
                ];
            });

        return response()->json($diagnoses);
    }

    // — Shared access check —
    // Both doctors and assistants can access any patient in the shared clinic
    private function authorizePatientAccess(Request $request, Patient $patient): void
    {
        $clinicId = $request->header('X-Clinic-ID');

        if ((string) $patient->clinic_id !== (string) $clinicId) {
            abort(403, 'This patient does not belong to your active clinic.');
        }
    }
}