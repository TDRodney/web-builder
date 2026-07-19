<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTenantNavigationRequest;
use Illuminate\Http\JsonResponse;

class TenantNavigationController extends Controller
{
    public function update(UpdateTenantNavigationRequest $request): JsonResponse
    {
        $tenant = app('currentTenant');

        /** @var array<string, mixed> $navigationConfig */
        $navigationConfig = $request->validated('navigation_config');

        $tenant->update([
            'navigation_config' => $navigationConfig,
        ]);
        $tenant->markSiteSetupCompleted();

        return response()->json([
            'status' => 'success',
            'message' => 'Navigation configuration saved successfully.',
            'navigation_config' => $tenant->navigation_config,
        ]);
    }
}
