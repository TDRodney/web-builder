<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceStorefront;
use App\Commerce\Contracts\CommerceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommerceStorefrontController extends Controller
{
    public function home(CommerceStorefront $storefront): Response
    {
        return $this->render($storefront->template('home')->published_config, ['data' => [], 'stale' => false, 'purchasingEnabled' => true], 'home');
    }

    public function collection(Request $request, CommerceStorefront $storefront, string $handle): Response
    {
        $resource = $storefront->collection($handle, $request->only(['sort', 'filters', 'cursor']));

        return $this->render($storefront->template('collection', $resource['data']->templateKey)->published_config, $resource, 'collection');
    }

    public function product(CommerceStorefront $storefront, string $handle): Response
    {
        $resource = $storefront->product($handle);

        return $this->render($storefront->template('product', $resource['data']->templateKey)->published_config, $resource, 'product');
    }

    public function cart(Request $request, CommerceProvider $provider): Response
    {
        $cart = $provider->cart($request->session()->get('commerce_cart_id'));
        $request->session()->put('commerce_cart_id', $cart->id);

        return $this->render(['schemaVersion' => 1, 'sections' => []], ['data' => $cart, 'stale' => false, 'purchasingEnabled' => true], 'cart');
    }

    public function addLine(Request $request, CommerceProvider $provider): JsonResponse
    {
        $validated = $request->validate(['variantId' => ['required', 'string'], 'quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $cart = $provider->cart($request->session()->get('commerce_cart_id'));
        $cart = $provider->addCartLine($cart->id, $validated['variantId'], $validated['quantity']);
        $request->session()->put('commerce_cart_id', $cart->id);

        return response()->json(['cart' => $cart]);
    }

    public function updateLine(Request $request, CommerceProvider $provider, string $lineId): JsonResponse
    {
        $validated = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:99']]);
        $cartId = $request->session()->get('commerce_cart_id');
        abort_unless(is_string($cartId), 404);
        $cart = $validated['quantity'] === 0 ? $provider->removeCartLine($cartId, $lineId) : $provider->updateCartLine($cartId, $lineId, $validated['quantity']);

        return response()->json(['cart' => $cart]);
    }

    public function removeLine(Request $request, CommerceProvider $provider, string $lineId): JsonResponse
    {
        $cartId = $request->session()->get('commerce_cart_id');
        abort_unless(is_string($cartId), 404);

        return response()->json(['cart' => $provider->removeCartLine($cartId, $lineId)]);
    }

    public function checkout(Request $request, CommerceProvider $provider): RedirectResponse
    {
        $cartId = $request->session()->get('commerce_cart_id');
        abort_unless(is_string($cartId), 422, 'Cart is empty.');

        return redirect()->away($provider->checkout($cartId));
    }

    private function render(array $template, array $resource, string $type): Response
    {
        $tenant = app('currentTenant');

        return Inertia::render('Tenant/CommerceStorefront', ['tenant' => $tenant->only(['id', 'subdomain', 'theme_config', 'navigation_config']), 'template' => $template, 'resource' => $resource, 'type' => $type]);
    }
}
