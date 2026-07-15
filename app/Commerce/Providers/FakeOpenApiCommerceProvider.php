<?php

namespace App\Commerce\Providers;

use App\Commerce\Contracts\CommerceProvider;
use App\Commerce\Data\CartData;
use App\Commerce\Data\CollectionData;
use App\Commerce\Data\FacetData;
use App\Commerce\Data\MoneyData;
use App\Commerce\Data\ProductData;
use App\Commerce\Data\VariantData;

class FakeOpenApiCommerceProvider implements CommerceProvider
{
    public function product(string $handle): ProductData
    {
        $price = new MoneyData('48.00', 'USD', '$48.00');

        return new ProductData($handle, $handle, str($handle)->replace('-', ' ')->title()->toString(), 'A considered everyday object from the connected catalog.', ['/images/commerce-placeholder.svg'], [new VariantData("{$handle}-default", 'Default', [], $price, true)]);
    }

    public function collection(string $handle, array $query = []): CollectionData
    {
        $products = collect(['linen-throw', 'stoneware-cup', 'canvas-tote', 'cedar-candle'])->map(fn (string $product) => $this->product($product))->all();

        return new CollectionData($handle, $handle, str($handle)->replace('-', ' ')->title()->toString(), $products, [new FacetData('availability', 'Availability', [['value' => 'in-stock', 'label' => 'In stock']])]);
    }

    public function cart(?string $cartId = null): CartData
    {
        return new CartData($cartId ?? 'fake-cart', [], new MoneyData('0.00', 'USD', '$0.00'), true);
    }

    public function addCartLine(string $cartId, string $variantId, int $quantity): CartData
    {
        return $this->cart($cartId);
    }

    public function updateCartLine(string $cartId, string $lineId, int $quantity): CartData
    {
        return $this->cart($cartId);
    }

    public function removeCartLine(string $cartId, string $lineId): CartData
    {
        return $this->cart($cartId);
    }

    public function checkout(string $cartId): string
    {
        return "https://checkout.example.test/{$cartId}";
    }
}
