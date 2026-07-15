<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'subdomain', 'theme_config', 'navigation_config', 'site_setup_completed_at'];

    protected $casts = [
        'theme_config' => 'array',
        'navigation_config' => 'array',
        'site_setup_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function contactSubmissions(): HasMany
    {
        return $this->hasMany(ContactSubmission::class);
    }

    public function commerceConnection(): HasOne
    {
        return $this->hasOne(CommerceConnection::class);
    }

    public function commerceTemplates(): HasMany
    {
        return $this->hasMany(CommerceTemplate::class);
    }

    public function isEligibleForInitialSiteKit(): bool
    {
        return $this->site_setup_completed_at === null
            && $this->theme_config === null
            && $this->navigation_config === null
            && $this->pages()->doesntExist();
    }

    public function markSiteSetupCompleted(): void
    {
        if ($this->site_setup_completed_at !== null) {
            return;
        }

        $this->update(['site_setup_completed_at' => now()]);
    }

    /**
     * Get the name of the tenant (defaults to owner user's workspace name).
     */
    public function getNameAttribute(): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $this->subdomain));
    }
}
