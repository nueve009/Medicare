<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultationController extends Controller
{
    public function index(Request $request)
    {
        $query = Consultation::with([
                'doctor:id,first_name,last_name',
                'patient:id,first_name,last_name',
                'prescriptions.generic:id,generic_name',
                'prescriptions.brand:id,brand_name',
                'diseases' => function ($q) {
                    $q->withPivot('id', 'type', 'status', 'symptoms', 'disease_name_snapshot');
                },
            ])
            ->where('clinic_id', '=', $request->header('X-Clinic-ID'))
            ->orderBy('consultation_date', 'desc');

        if ($request->query('patient_id')) {
            $query->where('patient_id', '=', $request->query('patient_id'));
        }

        return response()->json($query->paginate(20));
    }

    // 2. CREATE (POST /api/consultations)
    // Doctors only. Creates the consultation, diseases, and prescriptions in one transaction.
    public function store(Request $request)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Only doctors can create consultations.'], 403);
        }

        $validated = $request->validate([
            // Core fields
            'patient_id'           => 'required|exists:patients,id',
            'clinic_id'            => 'required|exists:clinics,id',
            'consultation_date'    => 'required|date',
            'chief_complaint'      => 'nullable|string',
            'notes'                => 'nullable|string',

            // Vital signs — all optional
            'blood_pressure'       => 'nullable|string|max:10',
            'heart_rate'           => 'nullable|integer|min:0|max:300',
            'respiratory_rate'     => 'nullable|integer|min:0|max:100',
            'temperature'          => 'nullable|numeric|min:30|max:45',
            'weight'               => 'nullable|numeric|min:0|max:500',
            'height'               => 'nullable|numeric|min:0|max:300',
            'oxygen_saturation'    => 'nullable|integer|min:0|max:100',

            // Diseases — optional array
            'diseases'                  => 'nullable|array',
            'diseases.*.disease_id'     => 'required_with:diseases|exists:diseases,id',
            'diseases.*.type'           => 'required_with:diseases|in:primary,secondary',
            'diseases.*.status'         => 'nullable|in:ongoing,treated,referred',
            'diseases.*.symptoms'       => 'nullable|string',

            // Prescriptions — optional array
            'prescriptions'                => 'nullable|array',
            'prescriptions.*.generic_id'   => 'required_with:prescriptions|exists:generics,id',
            'prescriptions.*.brand_id'     => 'required_with:prescriptions|exists:brands,id',
            'prescriptions.*.dosage'       => 'required_with:prescriptions|string|max:255',
            'prescriptions.*.frequency'    => 'required_with:prescriptions|string|max:255',
            'prescriptions.*.duration'     => 'required_with:prescriptions|string|max:255',
            'prescriptions.*.instructions' => 'nullable|string',
        ]);

        // Wrap everything in a transaction so nothing is half-saved if something fails
        $consultation = DB::transaction(function () use ($validated, $request) {

            $consultation = Consultation::create([
                'doctor_id'         => $request->user()->id,
                'patient_id'        => $validated['patient_id'],
                'clinic_id'         => $validated['clinic_id'],
                'consultation_date' => $validated['consultation_date'],
                'chief_complaint'   => $validated['chief_complaint'] ?? null,
                'notes'             => $validated['notes'] ?? null,
                'blood_pressure'    => $validated['blood_pressure'] ?? null,
                'heart_rate'        => $validated['heart_rate'] ?? null,
                'respiratory_rate'  => $validated['respiratory_rate'] ?? null,
                'temperature'       => $validated['temperature'] ?? null,
                'weight'            => $validated['weight'] ?? null,
                'height'            => $validated['height'] ?? null,
                'oxygen_saturation' => $validated['oxygen_saturation'] ?? null,
            ]);

            // Attach diseases to the pivot table if provided
            if (!empty($validated['diseases'])) {
                foreach ($validated['diseases'] as $d) {
                    $disease = \App\Models\Disease::find($d['disease_id']);

                    $consultation->diseases()->attach($d['disease_id'], [
                        'type'                  => $d['type'],
                        'status'                => $d['status'] ?? 'ongoing',
                        'symptoms'              => $d['symptoms'] ?? null,
                        'disease_name_snapshot' => $disease ? $disease->disease_name : 'Unknown',
                    ]);
                }
            }

            // Create prescriptions if provided
        if (!empty($validated['prescriptions'])) {
            foreach ($validated['prescriptions'] as $rx) {
                // Fetch the current names from the database
                $genericName = \App\Models\Generic::find($rx['generic_id'])?->generic_name;
                $brandName = \App\Models\Brand::find($rx['brand_id'])?->brand_name;

                Prescription::create([
                    'consultation_id'       => $consultation->id,
                    'generic_id'            => $rx['generic_id'],
                    'generic_name_snapshot' => $genericName, // Save the string!
                    'brand_id'              => $rx['brand_id'],
                    'brand_name_snapshot'   => $brandName,   // Save the string!
                    'dosage'                => $rx['dosage'],
                    'frequency'             => $rx['frequency'],
                    'duration'              => $rx['duration'],
                    'instructions'          => $rx['instructions'] ?? null,
                ]);
            }
        }

            return $consultation;
        });

        return response()->json([
            'message'      => 'Consultation created successfully',
            'consultation' => $consultation->load([
                'doctor:id,first_name,last_name',
                'patient:id,first_name,last_name',
                'diseases:id,disease_name',
                'prescriptions.generic:id,generic_name',
                'prescriptions.brand:id,brand_name',
            ]),
        ], 201);
    }

    // 3. READ ONE (GET /api/consultations/{consultation})
   public function show(Consultation $consultation)
    {
        return response()->json(
            $consultation->load([
                'doctor:id,first_name,last_name',
                'patient:id,first_name,last_name',
                'diseases' => function ($q) {
                    $q->withPivot('id', 'type', 'status', 'symptoms', 'disease_name_snapshot');
                },
                'prescriptions.generic:id,generic_name',
                'prescriptions.brand:id,brand_name',
            ])
        );
    }

    // 4. UPDATE (PUT/PATCH /api/consultations/{consultation})
    // Only updates consultation details and vitals — not diseases or prescriptions.
    // Diseases and prescriptions have their own endpoints for post-creation edits.
    public function update(Request $request, Consultation $consultation)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Only doctors can update consultations.'], 403);
        }

        $validated = $request->validate([
            'consultation_date' => 'sometimes|date',
            'chief_complaint'   => 'nullable|string',
            'notes'             => 'nullable|string',
            'blood_pressure'    => 'nullable|string|max:10',
            'heart_rate'        => 'nullable|integer|min:0|max:300',
            'respiratory_rate'  => 'nullable|integer|min:0|max:100',
            'temperature'       => 'nullable|numeric|min:30|max:45',
            'weight'            => 'nullable|numeric|min:0|max:500',
            'height'            => 'nullable|numeric|min:0|max:300',
            'oxygen_saturation' => 'nullable|integer|min:0|max:100',
        ]);

        $consultation->update($validated);

        return response()->json([
            'message'      => 'Consultation updated successfully',
            'consultation' => $consultation->load([
                'doctor:id,first_name,last_name',
                'patient:id,first_name,last_name',
                'diseases:id,disease_name',
                'prescriptions.generic:id,generic_name',
                'prescriptions.brand:id,brand_name',
            ]),
        ]);
    }

    // 5. DELETE (DELETE /api/consultations/{consultation})
    public function destroy(Request $request, Consultation $consultation)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Only doctors can delete consultations.'], 403);
        }

        $consultation->delete();

        return response()->json([
            'message' => 'Consultation archived successfully',
        ]);
    }
}