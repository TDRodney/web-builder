<?php

namespace App\Http\Controllers;

use App\Models\Page;

class TenantPublicSiteController extends Controller
{
    public function show(?string $slug = null)
    {
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

        // Render a traditional, blazing-fast Blade view for public traffic
        return view('tenant-public', [
            'blocks' => $page->published_config,
        ]);
    }
}
