<?php

namespace App\Http\Controllers;

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
        $homePage = $tenant->pages()->firstOrCreate(
            ['slug' => 'home'],
            [
                'draft_config' => [
                    [
                        'id' => 'hero-1',
                        'type' => 'HeroBlock',
                        'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                        'children' => [],
                    ],
                ],
            ]
        );

        $port = request()->getPort();
        $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';
        $centralHost = config('app.central_domain', 'domain.localhost').$portSuffix;
        $protocol = request()->getScheme();

        return Inertia::render('Tenant/Editor', [
            'tenant' => $tenant->only(['id', 'subdomain']),
            'page' => $homePage->only(['id', 'slug', 'draft_config']),
            'urls' => [
                'dashboard' => '/dashboard',
                'logout' => "{$protocol}://{$centralHost}/logout",
            ],
        ]);
    }
}
