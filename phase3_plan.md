# Phase 3 — Media & Asset Pipeline

> **Depends on**: Phase 1 (Block Registry) ✅, Phase 2 (Theming) ✅  
> **Unlocks**: Phase 4 (Advanced Blocks, Presets), ImageBlock, media-dependent templates

---

## Overview

Phase 3 establishes the file upload and media library system. Currently the codebase has no file handling — no uploads, no storage, no image blocks. This phase delivers:

1. A `Media` model and `media` DB table per tenant
2. A secure upload/list/delete CRUD API (`TenantMediaController`)
3. A visual `MediaPicker.vue` modal component in the editor
4. A functional `ImageBlock.vue` connected to the media library
5. A server-side image optimization pipeline (thumbnails + WebP)

---

## Stage 1 — DB Schema & Eloquent Model

### Migration: `create_media_table`

```bash
php artisan make:migration create_media_table --no-interaction
```

**Schema**:
```php
Schema::create('media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('filename');           // e.g. "hero-banner.jpg"
    $table->string('disk')->default('public'); // filesystem disk name
    $table->string('path');               // e.g. "media/1/abc123.jpg"
    $table->string('mime_type');          // e.g. "image/jpeg"
    $table->unsignedBigInteger('size');   // bytes
    $table->unsignedSmallInteger('width')->nullable();
    $table->unsignedSmallInteger('height')->nullable();
    $table->string('thumb_path')->nullable(); // 150px thumbnail path
    $table->timestamps();
});
```

### Model: `App\Models\Media`

```bash
php artisan make:model Media --factory --no-interaction
```

- **Fillable**: `tenant_id`, `filename`, `disk`, `path`, `mime_type`, `size`, `width`, `height`, `thumb_path`
- **Casts**: `size` → integer, `width` → integer, `height` → integer
- **Appended**: `url` accessor → `Storage::disk($this->disk)->url($this->path)`
- **Appended**: `thumb_url` accessor → `Storage::disk($this->disk)->url($this->thumb_path)` (null if no thumb)
- **Relationship**: `belongsTo(Tenant)`
- **Global Scope**: Apply same `TenantScope` pattern as `Page` model

Add to `Tenant` model:
```php
public function media(): HasMany
{
    return $this->hasMany(Media::class);
}
```

---

## Stage 2 — Upload & CRUD API

### Controller: `TenantMediaController`

```bash
php artisan make:controller TenantMediaController --no-interaction
```

**Routes** (add to tenant subdomain group in `web.php`):
```php
Route::get('/editor/media',        [TenantMediaController::class, 'index'])->name('tenant.media.index');
Route::post('/editor/media',       [TenantMediaController::class, 'store'])->name('tenant.media.store');
Route::delete('/editor/media/{media}', [TenantMediaController::class, 'destroy'])->name('tenant.media.destroy');
```

**`index()`**: Returns JSON list of tenant's media, ordered by `created_at` desc.
```php
return response()->json(
    $tenant->media()->orderByDesc('created_at')->get()
);
```

**`store()`**: Validates, stores, dispatches optimization job, returns JSON.
```php
$request->validate([
    'file' => ['required', 'file', 'image', 'max:5120', 'mimes:jpeg,png,gif,webp,svg'],
]);

$file = $request->file('file');
$path = $file->store("media/{$tenant->id}", 'public');

$media = $tenant->media()->create([
    'filename'  => $file->getClientOriginalName(),
    'disk'      => 'public',
    'path'      => $path,
    'mime_type' => $file->getMimeType(),
    'size'      => $file->getSize(),
    'width'     => null, // set by optimization job
    'height'    => null,
]);

// Dispatch async job for thumbnail + WebP conversion
OptimizeMediaJob::dispatch($media);

return response()->json($media->append(['url', 'thumb_url']));
```

**`destroy()`**: Ownership check, deletes file from disk, deletes DB record.

### Authorization

- All three endpoints: `auth()->id() !== $tenant->user_id` → 403
- `destroy()`: Use `TenantScope` to ensure the media record belongs to the current tenant

---

## Stage 3 — Media Picker UI Modal

### Component: `MediaPicker.vue`

**Location**: `resources/js/components/MediaPicker.vue`

**Responsibilities**:
- Modal overlay triggered by a "Choose Image" button in the inspector
- Emits `select(media: MediaItem)` event when the user picks an image
- Tabs: **Library** (existing uploads) | **Upload** (drag-and-drop file zone)

**Library tab**: 
- Fetches `GET /editor/media` on mount (using `useHttp`)
- Displays images in a responsive grid (4 columns, thumbnail previews)
- Single-click to select, shows selection highlight ring using `--theme-primary`
- Delete button per image (with two-click confirmation matching `BlockToolbar` pattern)

**Upload tab**:
- `<input type="file" multiple accept="image/*">` wrapped in a drag-and-drop zone
- Uploads to `POST /editor/media` with `multipart/form-data`
- Shows progress indicator and jumps to Library tab on success

**Usage in inspector**: The inspector sidebar in `Editor.vue` should render a `MediaPicker` button for `inspectorFields` of type `'media'` (a new field type to be added):
```php
['key' => 'src', 'label' => 'Image', 'type' => 'media'],
```

---

## Stage 4 — ImageBlock Integration

### Block Component: `ImageBlock.vue`

**Location**: `resources/js/components/BuilderBlocks/ImageBlock.vue`

**Props**: `nodeId`, `blockProps`

**Block props**:
```typescript
{
  src: string;          // Media URL or empty
  alt: string;          // Alt text for accessibility
  objectFit: 'cover' | 'contain' | 'fill';
  borderRadius: string; // defaults to 'var(--theme-border-radius)'
  width: string;        // e.g. '100%', '400px'
  height: string;       // e.g. '300px', 'auto'
}
```

**Rendering**:
- If `src` is set: render `<img :src="src" :alt="alt" :style="{objectFit, borderRadius, width, height}">` 
- If `src` is empty AND `isEditable`: render a placeholder div with upload icon and "Click to add image" text
- If `src` is empty AND NOT `isEditable`: render nothing (empty fragment)

**Register in all 4 locations** (per AGENTS.md checklist):
1. `ImageBlock.vue` component
2. `blockRegistry.ts` — import + `blockComponents` map
3. `config/blocks.php` — definition + add to all parent nesting lists
4. Run build + tests

**`config/blocks.php` entry**:
```php
'ImageBlock' => [
    'type'    => 'ImageBlock',
    'label'   => 'Image',
    'category' => 'media',
    'icon'    => 'image',
    'defaultProps' => [
        'src'          => '',
        'alt'          => '',
        'objectFit'    => 'cover',
        'borderRadius' => 'var(--theme-border-radius)',
        'width'        => '100%',
        'height'       => '300px',
        'padding'      => 0,
        'backgroundColor' => 'transparent',
    ],
    'inspectorFields' => [
        ['key' => 'src',          'label' => 'Image',         'type' => 'media'],
        ['key' => 'alt',          'label' => 'Alt Text',       'type' => 'text'],
        ['key' => 'objectFit',    'label' => 'Object Fit',     'type' => 'select', 'options' => [
            ['label' => 'Cover',   'value' => 'cover'],
            ['label' => 'Contain', 'value' => 'contain'],
            ['label' => 'Fill',    'value' => 'fill'],
        ]],
        ['key' => 'width',        'label' => 'Width',          'type' => 'text'],
        ['key' => 'height',       'label' => 'Height',         'type' => 'text'],
        ['key' => 'borderRadius', 'label' => 'Border Radius',  'type' => 'text'],
        ['key' => 'padding',      'label' => 'Padding (px)',   'type' => 'range', 'min' => 0, 'max' => 100],
    ],
],
```

---

## Stage 5 — Image Optimization Pipeline

### Job: `OptimizeMediaJob`

```bash
php artisan make:job OptimizeMediaJob --no-interaction
```

**Responsibilities** (runs on the `default` queue):
1. Read the uploaded file from storage
2. Detect image dimensions using PHP's `getimagesize()` or the `Intervention/Image` package
3. Generate a 150×150 thumbnail (cropped to cover), stored as `media/{tenant_id}/thumbs/{filename}`
4. Update the `media` record with `width`, `height`, `thumb_path`

> **Note**: Queue worker must be running: `php artisan queue:work`

> **Dependency**: This requires adding `intervention/image` to composer if full transformation is needed. For MVP, `getimagesize()` for dimension detection and native GD functions for thumbnail generation avoids new dependencies.

---

## Test Strategy

### New Test Files

- **`TenantMediaTest.php`** (Feature): 
  - Tenant owner can upload an image (returns 200 + JSON media object)
  - Upload validates file type (rejects PDF, text files)
  - Upload validates file size (rejects > 5MB)
  - Tenant owner can list media (returns array)
  - Tenant owner can delete their own media (file removed from disk)
  - Tenant cannot delete media belonging to another tenant (403)
  - Guest cannot upload (redirects to login)

### Verification Checklist

```bash
php artisan migrate
php artisan test --compact --filter=TenantMedia
npm run build
php artisan test --compact
```

---

## File Change Summary

| File | Action |
|---|---|
| `database/migrations/*_create_media_table.php` | **NEW** |
| `app/Models/Media.php` | **NEW** |
| `database/factories/MediaFactory.php` | **NEW** |
| `app/Models/Tenant.php` | **MODIFY** — add `hasMany(Media)` |
| `app/Http/Controllers/TenantMediaController.php` | **NEW** |
| `app/Jobs/OptimizeMediaJob.php` | **NEW** |
| `routes/web.php` | **MODIFY** — add 3 media routes |
| `resources/js/components/MediaPicker.vue` | **NEW** |
| `resources/js/components/BuilderBlocks/ImageBlock.vue` | **NEW** |
| `resources/js/lib/blockRegistry.ts` | **MODIFY** — add ImageBlock |
| `config/blocks.php` | **MODIFY** — add ImageBlock definition + nesting |
| `tests/Feature/TenantMediaTest.php` | **NEW** |
