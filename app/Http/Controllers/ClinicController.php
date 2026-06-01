<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    public function index()
    {
        return response()->json(Clinic::orderBy('name', 'asc')->get());
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:clinics,name',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $clinic = Clinic::create($validatedData);

        return response()->json([
            'message' => 'Clinic created successfully',
            'clinic' => $clinic
        ], 201);
    }

    public function show(Clinic $clinic)
    {
        // When viewing a specific clinic, we can also load the doctors that work there
        return response()->json($clinic->load('users:user_id,first_name,last_name,role'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255|unique:clinics,name,' . $clinic->clinic_id . ',clinic_id',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $clinic->update($validatedData);

        return response()->json([
            'message' => 'Clinic updated successfully',
            'clinic' => $clinic
        ]);
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return response()->json([
            'message' => 'Clinic archived successfully'
        ]);
    }
}