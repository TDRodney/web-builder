<?php

namespace App\Http\Controllers;

use App\Actions\Designs\BuildPageLayouts;
use App\Commerce\CommerceHydrator;
use App\Commerce\Contracts\CommerceProvider;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantEditorController extends Controller
{
    public function __construct(
        private CommerceHydrator $commerceHydrator,
        private CommerceProvider $commerceProvider,
    ) {}

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

        $previewOptions = $this->containsBlockType($currentPage->draft_config ?? [], 'ProductDetailBlock')
            ? $this->commerceProvider->previewOptions()
            : [];
        $requestedPreview = request()->string('commerce_preview')->toString();
        $selectedPreview = collect($previewOptions)
            ->where('resource', 'product')
            ->pluck('source')
            ->contains($requestedPreview)
                ? $requestedPreview
                : null;

        return Inertia::render('Tenant/Editor', [
            'tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']),
            'page' => $currentPage->only(['id', 'slug', 'title', 'is_homepage', 'draft_config']),
            'pages' => $pages,
            'page_layouts' => app(BuildPageLayouts::class)->handle(),
            'commerce_hydration' => $this->commerceHydrator->hydrate(
                $tenant,
                $currentPage->draft_config ?? [],
                $selectedPreview ? ['product' => $selectedPreview] : [],
            ),
            'commerce_preview' => [
                'selected' => $selectedPreview,
                'options' => $previewOptions,
            ],
            'urls' => [
                'dashboard' => '/dashboard',
                'logout' => "{$protocol}://{$centralHost}/logout",
                'live' => $publicPageUrl,
            ],
        ]);
    }

    /** @param array<int, array<string, mixed>> $nodes */
    private function containsBlockType(array $nodes, string $type): bool
    {
        foreach ($nodes as $node) {
            if (($node['type'] ?? null) === $type) {
                return true;
            }

            if (is_array($node['children'] ?? null) && $this->containsBlockType($node['children'], $type)) {
                return true;
            }
        }

        return false;
    }
}
