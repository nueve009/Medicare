<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index()
    {
        return response()->json(Clinic::orderBy('clinic_name', 'asc')->get());
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can create clinics.'], 403);
        }

        $validatedData = $request->validate([
            'clinic_name' => 'required|string|max:255|unique:clinics,clinic_name',
            'doctor_id'   => 'nullable|exists:users,id',
            'address'     => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $clinic = Clinic::create($validatedData);

        return response()->json([
            'message' => 'Clinic created successfully',
            'clinic'  => $clinic
        ], 201);
    }

    public function show(Clinic $clinic)
    {
        return response()->json($clinic->load('users:id,first_name,last_name,role'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can update clinics.'], 403);
        }

        $validatedData = $request->validate([
            'clinic_name'  => 'sometimes|string|max:255|unique:clinics,clinic_name,' . $clinic->id,
            'doctor_id'    => 'nullable|exists:users,id',
            'address'      => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $clinic->update($validatedData);

        return response()->json([
            'message' => 'Clinic updated successfully',
            'clinic'  => $clinic
        ]);
    }

    public function destroy(Request $request, Clinic $clinic)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized. Only admins can delete clinics.'], 403);
        }

        $clinic->delete();

        return response()->json([
            'message' => 'Clinic archived successfully'
        ]);
    }
}