<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrescriptionPdfController extends Controller
{
    public function generate(Request $request, Consultation $consultation)
    {
        // Validate signed URL
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link.');
        }

        // Load all needed relationships
        $consultation->load([
            'doctor:id,first_name,last_name,specialization,prc_id',
            'patient:id,first_name,last_name,gender,birthdate,address',
            'clinic:id,clinic_name,address,phone_number',
            'prescriptions.generic:id,generic_name',
            'prescriptions.brand:id,brand_name',
        ]);

        $doctor = $consultation->doctor;
        $patient = $consultation->patient;
        $clinic = $consultation->clinic;

        // Calculate patient age
        $age = $patient->birthdate
            ? Carbon::parse($patient->birthdate)->age
            : null;

        // Gender display
        $gender = $patient->gender
            ? strtoupper(substr($patient->gender, 0, 1))
            : '';

        // Build prescriptions array
        $prescriptions = collect($consultation->prescriptions)->map(function ($rx) {
            return [
                'generic_name' => $rx->generic?->generic_name ?? $rx->generic_name_snapshot ?? 'Unknown',
                'brand_name'   => $rx->brand?->brand_name ?? $rx->brand_name_snapshot ?? 'Unknown',
                'dosage'       => $rx->dosage,
                'frequency'    => $rx->frequency,
                'duration'     => $rx->duration,
                'instructions' => $rx->instructions ?? null,
            ];
        });

        $data = [
            'doctor' => [
                'name'           => $doctor->first_name . ' ' . $doctor->last_name,
                'specialization' => $doctor->specialization ?? 'General Practitioner',
                'prc_id'         => $doctor->prc_id ?? 'N/A',
            ],
            'patient' => [
                'name'    => $patient->last_name . ', ' . $patient->first_name,
                'age'     => $age ?? '—',
                'gender'  => $gender,
                'address' => $patient->address ?? null,
            ],
            'clinic' => [
                'name'    => $clinic->clinic_name,
                'address' => $clinic->address ?? null,
                'phone'   => $clinic->phone_number ?? null,
            ],
            'consultation' => [
                'date' => Carbon::parse($consultation->consultation_date)
                    ->format('m/d/Y'),
            ],
            'prescriptions' => $prescriptions,
        ];

        $pdf = Pdf::loadView('prescription', $data)
            ->setPaper('letter', 'portrait');

        $filename = 'prescription_' . $patient->last_name . '_' .
            Carbon::parse($consultation->consultation_date)->format('Ymd') . '.pdf';

        return $pdf->stream($filename);
    }

    public function generateSignedUrl(Request $request, Consultation $consultation)
    {
        // Only the attending doctor or clinic staff can generate the link
        $user = $request->user();
        if ($user->role === 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $url = \URL::temporarySignedRoute(
            'prescription.pdf',
            now()->addMinutes(15),
            ['consultation' => $consultation->id]
        );

        return response()->json(['url' => $url]);
    }
}