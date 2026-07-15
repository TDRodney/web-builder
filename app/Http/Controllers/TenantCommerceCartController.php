<?php

namespace App\Http\Controllers;

use App\Commerce\CommerceCartManager;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantCommerceCartController extends Controller
{
    public function __construct(private CommerceCartManager $cartManager) {}

    public function show(): JsonResponse
    {
        return $this->respond($this->cartManager->current($this->tenant()));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => ['required', 'string', 'max:150'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        return $this->respond($this->cartManager->add(
            $this->tenant(),
            $validated['variant_id'],
            $validated['quantity'],
        ));
    }

    public function update(Request $request, string $variantId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        return $this->respond($this->cartManager->update(
            $this->tenant(),
            $variantId,
            $validated['quantity'],
        ));
    }

    public function destroy(string $variantId): JsonResponse
    {
        return $this->respond($this->cartManager->remove($this->tenant(), $variantId));
    }

    public function checkout(): JsonResponse
    {
        return $this->respond($this->cartManager->checkout($this->tenant()));
    }

    /** @param array<string, mixed> $result */
    private function respond(array $result): JsonResponse
    {
        $status = match ($result['status'] ?? null) {
            'ready' => 200,
            'error' => 422,
            default => 503,
        };

        return response()->json($result, $status);
    }

    private function tenant(): Tenant
    {
        $tenant = app('currentTenant');

        abort_unless((bool) data_get($tenant->navigation_config, 'commerce.enabled', false), 404);

        return $tenant;
    }
}
