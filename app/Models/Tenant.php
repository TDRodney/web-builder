<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = ['user_id', 'subdomain'];

    public function user(): BelongsTo
    {
        return $table->belongsTo(User::class);
    }

    public function pages(): HasMany
    {
        return $table->hasMany(Page::class);
    }
}