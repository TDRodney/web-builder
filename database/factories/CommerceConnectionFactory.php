<?php

namespace Database\Factories;

use App\Models\CommerceConnection;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommerceConnection>
 */
class CommerceConnectionFactory extends Factory
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
            'provider' => 'fake-openapi',
            'store_identifier' => fake()->uuid(),
            'credentials' => ['token' => fake()->sha256()],
            'is_enabled' => true,
        ];
    }
}
