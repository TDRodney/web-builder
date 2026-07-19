<?php

namespace App\Http\Controllers;

use App\Actions\Designs\ApplySiteKit;
use App\Actions\Designs\BuildDesignLibrary;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantDesignLibraryController extends Controller
{
    public function index(BuildDesignLibrary $buildDesignLibrary): Response|RedirectResponse
    {
        $tenant = app('currentTenant');

        abort_unless(auth()->id() === $tenant->user_id, 403);

        if (! $tenant->isEligibleForInitialSiteKit()) {
            return redirect()->route('tenant.editor', ['tenant' => $tenant->subdomain]);
        }

        return Inertia::render('DesignLibrary', [
            'tenant' => $tenant->only(['id', 'subdomain']),
            'can_apply_site_kit' => true,
            'kits' => $buildDesignLibrary->handle(),
        ]);
    }

    /**
     * Apply a site kit to the current tenant workspace.
     */
    public function store(ApplySiteKit $applySiteKit, string $kit): array
    {
        $tenant = app('currentTenant');

        abort_unless(auth()->id() === $tenant->user_id, 403);

        return $applySiteKit->handle($tenant, $kit);
    }

    /**
     * Opt out of the site kit flow and start from a blank workspace.
     */
    public function startFromScratch(): array
    {
        $tenant = app('currentTenant');

        abort_unless(auth()->id() === $tenant->user_id, 403);

        if (! $tenant->isEligibleForInitialSiteKit()) {
            abort(422, 'Workspace is no longer eligible for the start-from-scratch flow.');
        }

        $tenant->markSiteSetupCompleted();

        return ['status' => 'success'];
    }
}
