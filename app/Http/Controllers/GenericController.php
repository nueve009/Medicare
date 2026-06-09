<?php

namespace App\Http\Controllers;

use App\Models\Generic;
use Illuminate\Http\Request;

class GenericController extends Controller
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

            $generics = Generic::whereIn('created_by', $doctorIds)
                ->with('brands')          // ← was withCount('brands')
                ->orderBy('generic_name', 'asc')
                ->get();
        } else {
            $generics = Generic::where('created_by', '=', $user->id)
                ->with('brands')          // ← was withCount('brands')
                ->orderBy('generic_name', 'asc')
                ->get();
        }

        return response()->json($generics);
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can create generics.',
            ], 403);
        }

        $validated = $request->validate([
            'generic_name' => 'required|string|max:255',
        ]);

        $generic = Generic::create([
            'created_by'   => $request->user()->id,
            'generic_name' => $validated['generic_name'],
        ]);

        return response()->json([
            'message' => 'Generic created successfully',
            'generic' => $generic,
        ], 201);
    }

    public function show(Request $request, Generic $generic)
    {
        $this->authorizeOwnership($request, $generic);

        return response()->json(
            $generic->load('brands')
        );
    }

    public function update(Request $request, Generic $generic)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can update generics.',
            ], 403);
        }

        $this->authorizeOwnership($request, $generic);

        $validated = $request->validate([
            'generic_name' => 'required|string|max:255',
        ]);

        $generic->update($validated);

        return response()->json([
            'message' => 'Generic updated successfully',
            'generic' => $generic,
        ]);
    }

    public function destroy(Request $request, Generic $generic)
    {
        if ($request->user()->role !== 'doctor') {
            return response()->json([
                'message' => 'Unauthorized. Only doctors can delete generics.',
            ], 403);
        }

        $this->authorizeOwnership($request, $generic);

        $generic->delete();

        return response()->json([
            'message' => 'Generic deleted successfully',
        ]);
    }

    private function authorizeOwnership(Request $request, Generic $generic): void
    {
        if ((string) $generic->created_by !== (string) $request->user()->id) {
            abort(403, 'You do not own this generic.');
        }
    }
}