<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    public function index()
    {
        // Eager load the relationships to prevent N+1 query problems
        $consultations = Consultation::with([
            'patient:id,first_name,last_name', 
            'doctor:user_id,first_name,last_name',
            'disease:disease_id,disease_name'
        ])->orderBy('consultation_date', 'desc')->paginate(20);

        return response()->json($consultations);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'disease_id' => 'nullable|exists:diseases,disease_id',
            'consultation_date' => 'required|date',
            'symptoms' => 'nullable|string',
            'diagnosis_notes' => 'nullable|string',
            'status' => 'nullable|in:scheduled,in_progress,completed,cancelled'
        ]);

        // Automatically assign the logged-in user as the doctor
        $validatedData['doctor_id'] = $request->user()->user_id;

        $consultation = Consultation::create($validatedData);

        return response()->json([
            'message' => 'Consultation logged successfully',
            'consultation' => $consultation->load(['patient', 'doctor', 'disease'])
        ], 201);
    }

    public function show(Consultation $consultation)
    {
        return response()->json($consultation->load(['patient', 'doctor', 'disease']));
    }

    public function update(Request $request, Consultation $consultation)
    {
        $validatedData = $request->validate([
            'disease_id' => 'nullable|exists:diseases,disease_id',
            'consultation_date' => 'sometimes|date',
            'symptoms' => 'nullable|string',
            'diagnosis_notes' => 'nullable|string',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled'
        ]);

        $consultation->update($validatedData);

        return response()->json([
            'message' => 'Consultation updated successfully',
            'consultation' => $consultation->load(['patient', 'doctor', 'disease'])
        ]);
    }

    public function destroy(Consultation $consultation)
    {
        $consultation->delete();

        return response()->json([
            'message' => 'Consultation archived successfully'
        ]);
    }
}