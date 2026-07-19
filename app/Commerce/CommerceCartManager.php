<?php

namespace App\Commerce;

use App\Commerce\Contracts\CommerceProvider;
use App\Models\Tenant;

class CommerceCartManager
{
    public function __construct(private CommerceProvider $provider) {}

    /** @return array<string, mixed> */
    public function current(Tenant $tenant): array
    {
        $result = $this->provider->cart($tenant, $this->state($tenant));
        $this->store($tenant, $result);

        return $result;
    }

    /** @return array<string, mixed> */
    public function add(Tenant $tenant, string $variantId, int $quantity): array
    {
        $result = $this->provider->addCartLine($tenant, $this->state($tenant), $variantId, $quantity);
        $this->store($tenant, $result);

        return $result;
    }

    /** @return array<string, mixed> */
    public function update(Tenant $tenant, string $variantId, int $quantity): array
    {
        $result = $this->provider->updateCartLine($tenant, $this->state($tenant), $variantId, $quantity);
        $this->store($tenant, $result);

        return $result;
    }

    /** @return array<string, mixed> */
    public function remove(Tenant $tenant, string $variantId): array
    {
        $result = $this->provider->removeCartLine($tenant, $this->state($tenant), $variantId);
        $this->store($tenant, $result);

        return $result;
    }

    /** @return array<string, mixed> */
    public function checkout(Tenant $tenant): array
    {
        return $this->provider->checkout($tenant, $this->state($tenant));
    }

    private function sessionKey(Tenant $tenant): string
    {
        return "commerce.cart.tenant.{$tenant->id}.{$this->provider->key()}";
    }

    /** @return array<string, mixed> */
    private function state(Tenant $tenant): array
    {
        $state = session($this->sessionKey($tenant), ['lines' => []]);

        return is_array($state) ? $state : ['lines' => []];
    }

    /** @param array<string, mixed> $result */
    private function store(Tenant $tenant, array $result): void
    {
        if (is_array($result['state'] ?? null)) {
            session()->put($this->sessionKey($tenant), $result['state']);
        }
    }
}
