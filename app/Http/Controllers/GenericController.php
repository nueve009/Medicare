<?php

namespace App\Http\Controllers;

use App\Models\Generic;
use Illuminate\Http\Request;

class GenericController extends Controller
{
    public function index()
    {
        return response()->json(Generic::with('brands')->orderBy('generic_name', 'asc')->paginate(50));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'generic_name' => 'required|string|max:255|unique:generics,generic_name',
        ]);

        $generic = Generic::create($validatedData);

        return response()->json([
            'message' => 'Generic medicine added successfully',
            'generic' => $generic->load('brands')
        ], 201);
    }

    public function show(Generic $generic)
    {
        return response()->json($generic->load('brands'));
    }

    public function update(Request $request, Generic $generic)
    {
        $validatedData = $request->validate([
            'generic_name' => 'sometimes|string|max:255|unique:generics,generic_name,' . $generic->id,
        ]);

        $generic->update($validatedData);

        return response()->json([
            'message' => 'Generic medicine updated successfully',
            'generic' => $generic->load('brands')
        ]);
    }

    public function destroy(Generic $generic)
    {
        $generic->delete();

        return response()->json([
            'message' => 'Generic medicine removed successfully'
        ]);
    }
}