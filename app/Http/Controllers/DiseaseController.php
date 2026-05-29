<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use Illuminate\Http\Request;

class DiseaseController extends Controller
{
    public function index()
    {
        return response()->json(Disease::orderBy('disease_name', 'asc')->paginate(50));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'disease_name' => 'required|string|max:255|unique:diseases,disease_name',
            'description' => 'nullable|string',
            'symptoms' => 'nullable|string',
        ]);

        $disease = Disease::create($validatedData);

        return response()->json([
            'message' => 'Disease added successfully',
            'disease' => $disease
        ], 201);
    }

    public function show(Disease $disease)
    {
        return response()->json($disease);
    }

    public function update(Request $request, Disease $disease)
    {
        $validatedData = $request->validate([
            'disease_name' => 'sometimes|string|max:255|unique:diseases,disease_name,' . $disease->id,
            'description' => 'nullable|string',
            'symptoms' => 'nullable|string',
        ]);

        $disease->update($validatedData);

        return response()->json([
            'message' => 'Disease updated successfully',
            'disease' => $disease
        ]);
    }

    public function destroy(Disease $disease)
    {
        $disease->delete();

        return response()->json([
            'message' => 'Disease removed successfully'
        ]);
    }
}