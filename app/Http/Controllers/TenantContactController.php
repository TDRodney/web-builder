<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class TenantContactController extends Controller
{
    /**
     * Submit contact form.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        // Rate limit by IP address: 5 attempts per minute
        $ip = $request->ip();
        $key = 'contact-form:'.$tenant->id.':'.$ip;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => "Too many attempts. Please try again in {$seconds} seconds.",
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $request->validate([
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'data' => ['required', 'array'],
        ]);

        $submission = $tenant->contactSubmissions()->create([
            'page_id' => $request->input('page_id'),
            'form_data' => $request->input('data'),
            'ip_address' => $ip,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been sent successfully.',
            'submission' => $submission,
        ], 201);
    }
}
