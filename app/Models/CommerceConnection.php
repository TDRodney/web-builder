<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Database\Factories\CommerceConnectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommerceConnection extends Model
{
    /** @use HasFactory<CommerceConnectionFactory> */
    use HasFactory;

    protected $fillable = ['tenant_id', 'provider', 'store_identifier', 'credentials', 'is_enabled', 'last_verified_at'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'is_enabled' => 'boolean',
            'last_verified_at' => 'immutable_datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
