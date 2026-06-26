<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;

class TenantPublicSiteController extends Controller
{
    public function show(string $slug = 'home')
    {
        // Fetch the page (TenantScope automatically limits this to the active tenant)
        // We look specifically at the read-only 'published_config'
        $page = Page::where('slug', $slug)->firstOrFail();

        if (!$page->published_config) {
            abort(404, 'This page has not been published yet.');
        }

        // Render a traditional, blazing-fast Blade view for public traffic
        return view('tenant-public', [
            'blocks' => $page->published_config
        ]);
    }
}