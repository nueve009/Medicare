<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $clinicId = $request->header('X-Clinic-ID');

        // Reject the request if the header is missing entirely
        if (!$clinicId) {
            return response()->json([
                'message' => 'No clinic selected. Please provide an X-Clinic-ID header.',
            ], 422);
        }

        // Confirm the authenticated user is actually assigned to the requested clinic
        $belongs = $request->user()
            ->clinics()
            ->where('clinics.id', $clinicId)
            ->exists();

        if (!$belongs) {
            return response()->json([
                'message' => 'Access denied. You are not assigned to this clinic.',
            ], 403);
        }

        // Bind the validated clinic ID to the request so controllers
        // can use $request->active_clinic_id without re-querying
        $request->merge(['active_clinic_id' => (int) $clinicId]);

        return $next($request);
    }
}