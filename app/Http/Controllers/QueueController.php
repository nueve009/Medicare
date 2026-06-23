<?php

namespace App\Http\Controllers;

use App\Models\QueueEntry;
use App\Models\Patient;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    // GET /api/queue — list today's queue for the clinic
    public function index(Request $request)
    {
        $clinicId = $request->header('X-Clinic-ID');

        $entries = QueueEntry::with('patient')
            ->where('clinic_id', $clinicId)
            ->whereDate('queued_at', today())
            ->orderBy('queued_at', 'asc')
            ->get()
            ->filter(fn($entry) => $entry->patient !== null)
            ->map(function ($entry) {
                return [
                    'queue_id'   => $entry->id,
                    'queued_at'  => $entry->queued_at,
                    'patient'    => [
                        'id'           => $entry->patient->id,
                        'first_name'   => $entry->patient->first_name,
                        'last_name'    => $entry->patient->last_name,
                        'gender'       => $entry->patient->gender,
                        'birthdate'    => $entry->patient->birthdate,
                        'phone_number' => $entry->patient->phone_number,
                        'email'        => $entry->patient->email,
                    ],
                ];
            })
            ->values(); // re-index after filter so JSON encodes as array

        return response()->json(['data' => $entries]);
    }

    // POST /api/queue — add a patient to the queue
    public function store(Request $request)
    {
        $clinicId = $request->header('X-Clinic-ID');

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
        ]);

        // Prevent duplicate queue entries for same patient today
        $exists = QueueEntry::where('clinic_id', $clinicId)
            ->where('patient_id', $validated['patient_id'])
            ->whereDate('queued_at', today())
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Patient is already in today\'s queue.',
            ], 409);
        }

        $entry = QueueEntry::create([
            'patient_id' => $validated['patient_id'],
            'clinic_id'  => $clinicId,
            'added_by'   => $request->user()->id,
            'queued_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Patient added to queue.',
            'queue'   => $entry,
        ], 201);
    }

    // DELETE /api/queue/{id} — remove from queue (after consultation or manual)
    public function destroy(Request $request, QueueEntry $queueEntry)
    {
        $clinicId = $request->header('X-Clinic-ID');

        if ((string) $queueEntry->clinic_id !== (string) $clinicId) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $queueEntry->delete();

        return response()->json(['message' => 'Patient removed from queue.']);
    }

    // DELETE /api/queue/by-patient/{patientId} — remove by patient ID (used after consultation save)
    public function destroyByPatient(Request $request, $patientId)
    {
        $clinicId = $request->header('X-Clinic-ID');

        QueueEntry::where('clinic_id', $clinicId)
            ->where('patient_id', $patientId)
            ->whereDate('queued_at', today())
            ->delete();

        return response()->json(['message' => 'Patient removed from queue.']);
    }
}