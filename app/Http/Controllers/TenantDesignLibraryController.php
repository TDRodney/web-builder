<?php

namespace App\Http\Controllers;

use App\Actions\Designs\ApplySiteKit;
use App\Actions\Designs\BuildDesignLibrary;
use Inertia\Inertia;
use Inertia\Response;

class TenantDesignLibraryController extends Controller
{
    public function index(BuildDesignLibrary $buildDesignLibrary): Response
    {
        $tenant = app('currentTenant');

        abort_unless(auth()->id() === $tenant->user_id, 403);

        return Inertia::render('DesignLibrary', [
            'tenant' => $tenant->only(['id', 'subdomain']),
            'can_apply_site_kit' => $tenant->isEligibleForInitialSiteKit(),
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
