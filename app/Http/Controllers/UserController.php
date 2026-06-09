<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = User::with('clinics:id,clinic_name')
            ->whereIn('role', ['doctor', 'assistant']);

        if ($request->has('role')) {
            $query->where('role', '=', $request->query('role'));
        }

        $users = $query->orderBy('last_name', 'asc')->get();

        return response()->json($users);
    }

    public function update(Request $request, User $user)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Role must never be changed
        $validated = $request->validate([
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'email'          => 'sometimes|email|unique:users,email,' . $user->id,
            'phone_number'   => 'sometimes|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'clinic_ids'     => 'sometimes|array',
            'clinic_ids.*'   => 'integer|exists:clinics,id',
        ]);

        // Update basic fields — never touch role
        $user->update(collect($validated)->except('clinic_ids')->toArray());

        // Sync clinic assignments if provided
        if (isset($validated['clinic_ids'])) {
            $user->clinics()->sync($validated['clinic_ids']);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user'    => $user->load('clinics:id,clinic_name'),
        ]);
    }
}