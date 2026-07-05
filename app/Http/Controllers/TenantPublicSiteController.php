<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class TenantPublicSiteController extends Controller
{
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
            'tenant' => $tenant,
            'page' => $page,
        ]);
    }
}
