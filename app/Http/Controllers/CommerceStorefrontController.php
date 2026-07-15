<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceStorefront;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommerceStorefrontController extends Controller
{
    public function collection(Request $request, CommerceStorefront $storefront, string $handle): Response
    {
        $resource = $storefront->collection($handle, $request->only(['sort', 'filters', 'cursor']));
        $template = $storefront->template('collection', $resource['data']->templateKey);

        return $this->render($template->published_config, $resource, 'collection');
    }

    public function product(CommerceStorefront $storefront, string $handle): Response
    {
        $resource = $storefront->product($handle);
        $template = $storefront->template('product', $resource['data']->templateKey);

        return $this->render($template->published_config, $resource, 'product');
    }

    public function cart(CommerceStorefront $storefront): Response
    {
        return $this->render(['schemaVersion' => 1, 'sections' => []], ['data' => $storefront, 'stale' => false, 'purchasingEnabled' => true], 'cart');
    }

    private function render(array $template, array $resource, string $type): Response
    {
        $tenant = app('currentTenant');

        return Inertia::render('Tenant/CommerceStorefront', ['tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']), 'template' => $template, 'resource' => $resource, 'type' => $type]);
    }
}
