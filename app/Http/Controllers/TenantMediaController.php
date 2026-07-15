<?php

namespace App\Http\Controllers;

use App\Jobs\OptimizeMediaJob;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TenantMediaController extends Controller
{
    /**
     * Return all media items for the current tenant, newest first.
     */
    public function index(): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $media = $tenant->media()->orderByDesc('created_at')->get()->append(['url', 'thumb_url']);

        return response()->json($media);
    }

    /**
     * Upload a new file and dispatch the optimization job.
     */
    public function store(Request $request): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => ['required', 'file', 'image', 'max:5120', 'mimes:jpeg,png,gif,webp,svg'],
        ]);

        $file = $request->file('file');
        $path = $file->store("media/{$tenant->id}", 'public');

        $media = $tenant->media()->create([
            'filename' => $file->getClientOriginalName(),
            'disk' => 'public',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => null,
            'height' => null,
        ]);

        OptimizeMediaJob::dispatch($media);

        return response()->json($media->append(['url', 'thumb_url']), 201);
    }

    /**
     * Delete a media item and remove its files from storage.
     */
    public function destroy(Media $media): JsonResponse
    {
        $tenant = app('currentTenant');

        if (auth()->id() !== $tenant->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($media->tenant_id !== $tenant->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $disk = Storage::disk($media->disk);

        if ($disk->exists($media->path)) {
            $disk->delete($media->path);
        }

        if ($media->thumb_path && $disk->exists($media->thumb_path)) {
            $disk->delete($media->thumb_path);
        }

        $media->delete();

        return response()->json(['status' => 'success', 'message' => 'Media deleted successfully.']);
    }
}
