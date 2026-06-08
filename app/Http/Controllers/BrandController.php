<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Generic;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'assistant') {
            $clinicId = $request->header('X-Clinic-ID');
            $doctorIds = \App\Models\Clinic::find($clinicId)
                ->users()
                ->where('role', '=', 'doctor')
                ->pluck('users.id');

            $brands = Brand::whereIn('created_by', $doctorIds)
                ->with('generic:id,generic_name')
                ->orderBy('brand_name', 'asc')
                ->get();
        } else {
            $brands = Brand::where('created_by', '=', $user->id)
                ->with('generic:id,generic_name')
                ->orderBy('brand_name', 'asc')
                ->get();
        }

        return response()->json($brands);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can create brands.',
            ], 403);
        }

        $validated = $request->validate([
            'generic_id' => 'required|integer|exists:generics,id',
            'brand_name' => 'required|string|max:255',
        ]);

        $generic = Generic::find($validated['generic_id']);
        if ((string) $generic->created_by !== (string) $request->user()->id) {
            return response()->json([
                'message' => 'You can only add brands to your own generics.',
            ], 403);
        }

        $brand = Brand::create([
            'created_by' => $request->user()->id,
            'generic_id' => $validated['generic_id'],
            'brand_name' => $validated['brand_name'],
        ]);

        return response()->json([
            'message' => 'Brand created successfully',
            'brand'   => $brand->load('generic:id,generic_name'),
        ], 201);
    }

    public function show(Request $request, Brand $brand)
    {
        $this->authorizeOwnership($request, $brand);

        return response()->json(
            $brand->load('generic:id,generic_name')
        );
    }

    public function update(Request $request, Brand $brand)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can update brands.',
            ], 403);
        }

        $this->authorizeOwnership($request, $brand);

        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
            'generic_id' => 'sometimes|integer|exists:generics,id',
        ]);

        if (isset($validated['generic_id'])) {
            $generic = Generic::find($validated['generic_id']);
            if ((string) $generic->created_by !== (string) $request->user()->id) {
                return response()->json([
                    'message' => 'You can only reassign brands to your own generics.',
                ], 403);
            }
        }

        $brand->update($validated);

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand'   => $brand->load('generic:id,generic_name'),
        ]);
    }

    public function destroy(Request $request, Brand $brand)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can delete brands.',
            ], 403);
        }

        $this->authorizeOwnership($request, $brand);

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully',
        ]);
    }

    private function authorizeOwnership(Request $request, Brand $brand): void
    {
        if ((string) $brand->created_by !== (string) $request->user()->id) {
            abort(403, 'You do not own this brand.');
        }
    }
}