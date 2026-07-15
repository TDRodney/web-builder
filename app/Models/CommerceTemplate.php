<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Database\Factories\CommerceTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceTemplate extends Model
{
    /** @use HasFactory<CommerceTemplateFactory> */
    use HasFactory;

    protected $fillable = ['tenant_id', 'type', 'key', 'label', 'is_default', 'draft_config', 'published_config'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'draft_config' => 'array',
            'published_config' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
