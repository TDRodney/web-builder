<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;


class TenantEditorController extends Controller
{
    public function edit(): Response
    {
        $tenant = app('currentTenant'); // Resolve from service container

        if (auth()->id() !== $tenant->user_id) {
            abort(403, 'Unauthorized access to this tenant workspace.');
        }

        // Because of our TenantScope, this only searches pages belonging to this tenant.
        // If no home page found. Create template starter. TODO: Create better template starter somewhere else.
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'draft_config' => [
                    ['id' => 'hero-1', 'type' => 'HeroBlock', 'styles' => ['padding' => 40, 'backgroundColor' => '#ffffff']]
                ]
            ]
        );

        // Hydrate the reactive Vue 3 frontend canvas via Inertia
        return Inertia::render('Tenant/Editor', [
            'tenant' => $tenant->only(['id', 'subdomain']),
            'page' => $homePage->only(['id', 'slug', 'draft_config'])
        ]);
    }
}

