<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceCartManager;
use Inertia\Inertia;
use Inertia\Response;

class TenantFixtureCheckoutController extends Controller
{
    public function __construct(private CommerceCartManager $cartManager) {}

    public function show(): Response
    {
        $tenant = app('currentTenant');
        abort_unless((bool) data_get($tenant->navigation_config, 'commerce.enabled', false), 404);
        $result = $this->cartManager->current($tenant);

        abort_if(($result['cart']['itemCount'] ?? 0) === 0, 404, 'The fixture checkout has no cart items.');

        return Inertia::render('Tenant/FixtureCheckout', [
            'tenant' => $tenant->only(['id', 'subdomain', 'theme_config']),
            'cart' => $result['cart'],
        ]);
    }
}
