<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    // 1. UPDATE (PUT/PATCH /api/prescriptions/{prescription})
    // Edits an existing prescription after a consultation has been saved.
    public function update(Request $request, Prescription $prescription)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Only doctors can update prescriptions.'], 403);
        }

        $validated = $request->validate([
            'generic_id'   => 'sometimes|exists:generics,id',
            'brand_id'     => 'sometimes|exists:brands,id',
            'dosage'       => 'sometimes|string|max:255',
            'frequency'    => 'sometimes|string|max:255',
            'duration'     => 'sometimes|string|max:255',
            'instructions' => 'nullable|string',
        ]);

        $prescription->update($validated);

        return response()->json([
            'message'      => 'Prescription updated successfully',
            'prescription' => $prescription->load([
                'generic:id,generic_name',
                'brand:id,brand_name',
            ]),
        ]);
    }

    // 2. DELETE (DELETE /api/prescriptions/{prescription})
    // Removes a prescription from a consultation.
    public function destroy(Request $request, Prescription $prescription)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json(['message' => 'Unauthorized. Only doctors can delete prescriptions.'], 403);
        }

        $prescription->delete();

        return response()->json([
            'message' => 'Prescription removed successfully',
        ]);
    }
}