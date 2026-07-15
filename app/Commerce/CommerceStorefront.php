<?php

namespace App\Commerce;

use App\Commerce\Contracts\CommerceProvider;
use App\Models\CommerceTemplate;
use Illuminate\Support\Facades\Cache;

class CommerceStorefront
{
    public function __construct(private readonly CommerceProvider $provider) {}

    public function product(string $handle): array
    {
        return $this->remember("product:{$handle}", fn () => $this->provider->product($handle));
    }

    public function collection(string $handle, array $query = []): array
    {
        return $this->remember('collection:'.$handle.':'.hash('xxh3', serialize($query)), fn () => $this->provider->collection($handle, $query));
    }

    public function template(string $type, ?string $key = null): CommerceTemplate
    {
        return CommerceTemplate::query()->where('type', $type)->whereNotNull('published_config')
            ->when($key, fn ($query) => $query->where('key', $key))
            ->when(! $key, fn ($query) => $query->where('is_default', true))
            ->first() ?? CommerceTemplate::query()->where('type', $type)->whereNotNull('published_config')->where('is_default', true)->firstOrFail();
    }

    private function remember(string $key, callable $callback): array
    {
        $tenantId = app('currentTenant')->id;
        $cacheKey = "commerce:{$tenantId}:{$key}";
        try {
            $value = $callback();
            $payload = ['data' => $value, 'stale' => false, 'purchasingEnabled' => true];
            Cache::put($cacheKey, $payload, now()->addMinutes(10));
            Cache::put("{$cacheKey}:stale", $payload, now()->addDays(2));

            return $payload;
        } catch (\Throwable $exception) {
            $stale = Cache::get("{$cacheKey}:stale");
            if (! $stale) {
                throw $exception;
            }

            return [...$stale, 'stale' => true, 'purchasingEnabled' => false];
        }
    }
}
