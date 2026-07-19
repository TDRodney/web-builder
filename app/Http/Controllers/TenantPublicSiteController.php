<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceCartManager;
use App\Commerce\CommerceHydrator;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class TenantPublicSiteController extends Controller
{
    public function __construct(
        private CommerceHydrator $commerceHydrator,
        private CommerceCartManager $cartManager,
    ) {}

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

        if (! $page->is_published || ! $page->published_config) {
            abort(404, 'This page is not available.');
        }

        $requestedProduct = request()->string('commerce_product')->toString();
        $sourceOverrides = preg_match('/^[A-Za-z0-9._-]{1,100}$/', $requestedProduct) === 1
            ? ['product' => $requestedProduct]
            : [];
        $commerceEnabled = (bool) data_get($tenant->navigation_config, 'commerce.enabled', false);

        return Inertia::render('Tenant/PublicPage', [
            'tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']),
            'page' => $page,
            'commerce_hydration' => $this->commerceHydrator->hydrate($tenant, $page->published_config, $sourceOverrides),
            'commerce_cart' => $commerceEnabled ? $this->cartManager->current($tenant)['cart'] : null,
            'commerce_enabled' => $commerceEnabled,
        ]);
    }
}
