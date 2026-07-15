<?php

namespace App\Http\Controllers;

use App\Actions\Designs\BuildPageLayouts;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantEditorController extends Controller
{
    public function edit(): Response|RedirectResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            abort(403, 'Unauthorized access to this tenant workspace.');
        }

        if ($tenant->isEligibleForInitialSiteKit()) {
            return redirect()->route('tenant.designs.index', ['tenant' => $tenant->subdomain]);
        }

        $slug = request()->query('page');
        if ($slug) {
            $currentPage = $tenant->pages()->where('slug', $slug)->firstOrFail();
        } else {
            $currentPage = $tenant->pages()->where('is_homepage', true)->first()
                ?? $tenant->pages()->firstOrCreate(
                    ['slug' => 'home'],
                    [
                        'title' => 'Home',
                        'is_homepage' => true,
                        'draft_config' => [
                            [
                                'id' => 'hero-1',
                                'type' => 'HeroBlock',
                                'props' => ['padding' => 40, 'backgroundColor' => 'transparent', 'headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                                'children' => [],
                            ],
                        ],
                    ]
                );
        }

        $pages = $tenant->pages()
            ->select(['id', 'slug', 'title', 'is_homepage', 'sort_order'])
            ->orderBy('sort_order')
            ->get();

        $port = request()->getPort();
        $portSuffix = ($port && ! in_array($port, [80, 443])) ? ":{$port}" : '';
        $centralHost = config('app.central_domain', 'domain.localhost').$portSuffix;
        $protocol = request()->getScheme();
        $publicPageUrl = route('tenant.page.public', [
            'tenant' => $tenant->subdomain,
            'slug' => $currentPage->is_homepage ? null : $currentPage->slug,
        ]);

        return Inertia::render('Tenant/Editor', [
            'tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']),
            'page' => $currentPage->only(['id', 'slug', 'title', 'is_homepage', 'draft_config']),
            'pages' => $pages,
            'page_layouts' => app(BuildPageLayouts::class)->handle(),
            'urls' => [
                'dashboard' => '/dashboard',
                'logout' => "{$protocol}://{$centralHost}/logout",
                'live' => $publicPageUrl,
            ],
        ]);
    }
}
