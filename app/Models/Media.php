<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'filename',
        'disk',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'thumb_path',
    ];

    protected $appends = ['url', 'thumb_url'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the publicly accessible URL for the media file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * Get the thumbnail URL, or null if no thumbnail has been generated yet.
     */
    public function getThumbUrlAttribute(): ?string
    {
        if (! $this->thumb_path) {
            return null;
        }

        return Storage::disk($this->disk)->url($this->thumb_path);
    }
}
