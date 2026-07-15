<?php

namespace Database\Factories;

use App\Models\CommerceTemplate;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommerceTemplate>
 */
class CommerceTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'type' => 'product',
            'key' => 'default',
            'label' => 'Default product',
            'is_default' => true,
            'draft_config' => ['schemaVersion' => 1, 'sections' => []],
            'published_config' => null,
        ];
    }
}
