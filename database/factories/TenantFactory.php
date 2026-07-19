<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subdomain' => $this->faker->unique()->slug(2),
            'site_setup_completed_at' => now(),
        ];
    }

    public function awaitingSiteSetup(): static
    {
        return $this->state(fn (): array => [
            'site_setup_completed_at' => null,
        ]);
    }

    /**
     * Create a default homepage for the tenant.
     */
    public function withHomePage(): static
    {
        return $this->afterCreating(function (Tenant $tenant) {
            $tenant->pages()->create([
                'slug' => 'home',
                'title' => 'Home',
                'is_homepage' => true,
                'draft_config' => [
                    [
                        'id' => 'hero-1',
                        'type' => 'HeroBlock',
                        'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                        'children' => [],
                    ],
                ],
                'published_config' => [
                    [
                        'id' => 'hero-1',
                        'type' => 'HeroBlock',
                        'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                        'children' => [],
                    ],
                ],
            ]);
        });
    }
}
