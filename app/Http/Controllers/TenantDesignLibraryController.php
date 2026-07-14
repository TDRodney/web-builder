<?php

namespace App\Http\Controllers;

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
}
