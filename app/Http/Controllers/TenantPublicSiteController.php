<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceHydrator;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class TenantPublicSiteController extends Controller
{
    public function __construct(private CommerceHydrator $commerceHydrator) {}

    public function show(?string $slug = null): Response
    {
        $tenant = app('currentTenant');

        if (empty($slug)) {
            $page = Page::where('is_homepage', true)->first();

            if (! $page) {
                $page = Page::where('slug', 'home')->first();
            }
        } else {
            $page = Page::where('slug', $slug)->first();
        }

        if (! $page) {
            abort(404, 'Page not found.');
        }

        if (! $page->published_config) {
            abort(404, 'This page has not been published yet.');
        }

        return Inertia::render('Tenant/PublicPage', [
            'tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']),
            'page' => $page,
            'commerce_hydration' => $this->commerceHydrator->hydrate($tenant, $page->published_config),
        ]);
    }
}
