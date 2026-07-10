<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['tenant_id', 'slug', 'title', 'is_homepage', 'sort_order', 'draft_config', 'published_config'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Get the attributes that should be cast.
     * Laravel 11 style method-based casting.
     */
    protected function casts(): array
    {
        return [
            'is_homepage' => 'boolean',
            'draft_config' => 'array',
            'published_config' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
