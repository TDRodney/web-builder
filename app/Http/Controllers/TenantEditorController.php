<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class TenantEditorController extends Controller
{
    public function edit(): Response
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            abort(403, 'Unauthorized access to this tenant workspace.');
        }

        // Because of our TenantScope, this only searches pages belonging to this tenant.
        $homePage = Page::firstOrCreate(
            ['slug' => 'home'],
            [
                'draft_config' => [
                    [
                        'id' => 'hero-1',
                        'type' => 'HeroBlock',
                        'styles' => ['padding' => 40, 'backgroundColor' => '#ffffff'],
                        'content' => ['headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                    ],
                ],
            ]
        );

        return Inertia::render('Tenant/Editor', [
            'tenant' => $tenant->only(['id', 'subdomain']),
            'page' => $homePage->only(['id', 'slug', 'draft_config']),
        ]);
    }
}
