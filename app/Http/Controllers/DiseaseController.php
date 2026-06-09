<?php

namespace App\Http\Controllers;

use App\Models\ConsultationDisease;
use App\Models\Disease;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    public function index(Request $request)
    {
        $diseases = Disease::where('clinic_id', $request->header('X-Clinic-ID'))
            ->withCount([
                // Total diagnoses count
                'consultations as total_diagnoses_count',
                // Active diagnoses count — use whereIn on the pivot table directly
                'consultations as active_diagnoses_count' => function ($query) {
                    $query->whereIn('consultation_diseases.status', ['ongoing', 'referred']);
                },
            ])
            ->orderBy('disease_name', 'asc')
            ->get();

        return response()->json($diseases);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can create diseases.',
            ], 403);
        }

        $validated = $request->validate([
            'disease_name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('diseases')->where(fn($query) => 
                    $query->where('clinic_id', $request->header('X-Clinic-ID'))
                        ->whereNull('deleted_at')
                ),
            ],
            'description'  => 'nullable|string',
            'symptoms'     => 'nullable|string',
        ]);

        $disease = Disease::create([
            'clinic_id'    => $request->header('X-Clinic-ID'),
            'created_by'   => $request->user()->id,
            'disease_name' => $validated['disease_name'],
            'description'  => $validated['description'] ?? null,
            'symptoms'     => $validated['symptoms'] ?? null,
        ]);

        return response()->json([
            'message' => 'Disease created successfully',
            'disease' => $disease,
        ], 201);
    }

    public function show(Request $request, Disease $disease)
    {
        $this->authorizeClinicalAccess($request, $disease);

        return response()->json(
            $disease->loadCount([
                'consultations as total_diagnoses_count',
                'consultations as active_diagnoses_count' => function ($query) {
                    $query->wherePivotIn('status', ['ongoing', 'referred']);
                },
            ])
        );
    }

    public function update(Request $request, Disease $disease)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can update diseases.',
            ], 403);
        }

        $this->authorizeClinicalAccess($request, $disease);

        $validated = $request->validate([
            'disease_name' => [
                'sometimes',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('diseases')->where(fn($query) =>
                    $query->where('clinic_id', $request->header('X-Clinic-ID'))
                        ->whereNull('deleted_at')
                )->ignore($disease->id),
            ],
            'description'  => 'nullable|string',
            'symptoms'     => 'nullable|string',
        ]);

        $disease->update($validated);

        return response()->json([
            'message' => 'Disease updated successfully',
            'disease' => $disease,
        ]);
    }

    public function destroy(Request $request, Disease $disease)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can delete diseases.',
            ], 403);
        }

        $this->authorizeClinicalAccess($request, $disease);

        $activeCount = ConsultationDisease::where('disease_id', $disease->id)
            ->whereIn('status', ['ongoing', 'referred'])
            ->count();

        if ($activeCount > 0) {
            return response()->json([
                'message' => "Cannot archive this disease. {$activeCount} patient(s) are still actively diagnosed with it. Resolve all active cases before archiving.",
            ], 422);
        }

        $disease->delete();

        return response()->json([
            'message' => 'Disease archived successfully',
        ]);
    }

    public function patients(Request $request, Disease $disease)
    {
        $this->authorizeClinicalAccess($request, $disease);

        $diagnoses = ConsultationDisease::with([
                'consultation.patient:id,first_name,last_name,gender,birthdate',
                'consultation.prescriptions.generic:id,generic_name',
                'consultation.prescriptions.brand:id,brand_name',
            ])
            ->where('disease_id', $disease->id)
            ->whereHas('consultation', function ($query) use ($request) {
                $query->where('clinic_id', $request->header('X-Clinic-ID'));
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($diagnosis) {
                return [
                    'diagnosis_id'    => $diagnosis->id,
                    'patient'         => $diagnosis->consultation->patient,
                    'status'          => $diagnosis->status,
                    'type'            => $diagnosis->type,
                    'symptoms'        => $diagnosis->symptoms,
                    'disease_name'    => $diagnosis->disease_name_snapshot,
                    'diagnosed_at'    => $diagnosis->consultation->consultation_date,
                    'consultation_id' => $diagnosis->consultation_id,
                    'prescriptions'   => $diagnosis->consultation->prescriptions->map(function ($rx) {
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

        return response()->json([
            'disease' => [
                'id'           => $disease->id,
                'disease_name' => $disease->disease_name,
                'description'  => $disease->description,
                'deleted_at'   => $disease->deleted_at,
            ],
            'diagnoses' => $diagnoses,
        ]);
    }

    public function updateDiagnosisStatus(Request $request, Disease $disease, ConsultationDisease $diagnosis)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can update diagnosis status.',
            ], 403);
        }

        $validated = $request->validate([
            'status'   => 'required|in:ongoing,treated,referred',
            'symptoms' => 'nullable|string',
        ]);

        $diagnosis->update($validated);

        return response()->json([
            'message'   => 'Diagnosis status updated successfully',
            'diagnosis' => $diagnosis,
        ]);
    }

    private function authorizeClinicalAccess(Request $request, Disease $disease): void
    {
        if ((string) $disease->clinic_id !== (string) $request->header('X-Clinic-ID')) {
            abort(403, 'This disease does not belong to your active clinic.');
        }
    }
}