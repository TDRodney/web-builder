<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantNavigationController extends Controller
{
    /**
     * Update the tenant's navigation configuration.
     */
    public function update(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'navigation_config' => ['required', 'array'],
            'navigation_config.header' => ['required', 'array'],
            'navigation_config.footer' => ['required', 'array'],
        ]);

        $tenant->update([
            'navigation_config' => $validated['navigation_config'],
        ]);
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => 'Navigation configuration saved successfully.',
            'navigation_config' => $tenant->navigation_config,
        ]);
    }
}
