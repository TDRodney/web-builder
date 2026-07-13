<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word().'.jpg';

        return [
            'tenant_id' => Tenant::factory(),
            'filename' => $filename,
            'disk' => 'public',
            'path' => 'media/test/'.$filename,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(50000, 2000000),
            'width' => fake()->numberBetween(400, 4000),
            'height' => fake()->numberBetween(300, 3000),
            'thumb_path' => null,
        ];
    }
}
