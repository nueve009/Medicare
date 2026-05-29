<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json(Brand::with('generic')->orderBy('brand_name', 'asc')->paginate(50));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'generic_id' => 'required|exists:generics,id',
            'brand_name' => 'required|string|max:255|unique:brands,brand_name',
        ]);

        $brand = Brand::create($validatedData);

        return response()->json([
            'message' => 'Brand added successfully',
            'brand' => $brand->load('generic')
        ], 201);
    }

    public function show(Brand $brand)
    {
        return response()->json($brand->load('generic'));
    }

    public function update(Request $request, Brand $brand)
    {
        $validatedData = $request->validate([
            'generic_id' => 'sometimes|exists:generics,id',
            'brand_name' => 'sometimes|string|max:255|unique:brands,brand_name,' . $brand->id,
        ]);

        $brand->update($validatedData);

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand' => $brand->load('generic')
        ]);
    }

    public function destroy(Brand $brand)
    {
        $brand->delete();

        return response()->json([
            'message' => 'Brand removed successfully'
        ]);
    }
}