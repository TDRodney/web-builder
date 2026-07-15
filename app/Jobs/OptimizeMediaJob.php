<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class OptimizeMediaJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Media $media) {}

    /**
     * Execute the job.
     *
     * Detects image dimensions and generates a 150×150 thumbnail using native GD.
     * No external dependencies required.
     */
    public function handle(): void
    {
        $disk = Storage::disk($this->media->disk);

        if (! $disk->exists($this->media->path)) {
            return;
        }

        $absolutePath = $disk->path($this->media->path);

        // Detect dimensions
        $sizeInfo = @getimagesize($absolutePath);

        if (! $sizeInfo) {
            return;
        }

        [$srcWidth, $srcHeight, $imageType] = $sizeInfo;

        $this->media->update([
            'width' => $srcWidth,
            'height' => $srcHeight,
        ]);

        // Only generate thumbnails for supported GD image types (not SVG)
        $supportedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];

        if (! in_array($imageType, $supportedTypes)) {
            return;
        }

        $thumbSize = 150;

        $srcImage = match ($imageType) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG => @imagecreatefrompng($absolutePath),
            IMAGETYPE_GIF => @imagecreatefromgif($absolutePath),
            IMAGETYPE_WEBP => @imagecreatefromwebp($absolutePath),
            default => null,
        };

        if (! $srcImage) {
            return;
        }

        // Calculate crop dimensions for cover-style 1:1 thumbnail
        $scale = max($thumbSize / $srcWidth, $thumbSize / $srcHeight);
        $newWidth = (int) round($srcWidth * $scale);
        $newHeight = (int) round($srcHeight * $scale);
        $offsetX = (int) round(($newWidth - $thumbSize) / 2);
        $offsetY = (int) round(($newHeight - $thumbSize) / 2);

        $thumbImage = imagecreatetruecolor($thumbSize, $thumbSize);

        // Preserve transparency for PNG/GIF
        if ($imageType === IMAGETYPE_PNG || $imageType === IMAGETYPE_GIF) {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
            $transparent = imagecolorallocatealpha($thumbImage, 0, 0, 0, 127);
            imagefilledrectangle($thumbImage, 0, 0, $thumbSize, $thumbSize, $transparent);
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $srcWidth, $srcHeight);
        imagecopyresampled($thumbImage, $resized, 0, 0, $offsetX, $offsetY, $thumbSize, $thumbSize, $thumbSize, $thumbSize);

        imagedestroy($resized);
        imagedestroy($srcImage);

        // Save thumbnail to a temp buffer and upload to storage
        ob_start();
        imagejpeg($thumbImage, null, 85);
        $thumbData = ob_get_clean();
        imagedestroy($thumbImage);

        $thumbDir = dirname($this->media->path);
        $thumbFilename = pathinfo($this->media->filename, PATHINFO_FILENAME).'-thumb.jpg';
        $thumbPath = "{$thumbDir}/thumbs/{$thumbFilename}";

        $disk->put($thumbPath, $thumbData);

        $this->media->update(['thumb_path' => $thumbPath]);
    }
}
