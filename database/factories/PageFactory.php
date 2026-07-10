<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->words(2, true);

        return [
            'tenant_id' => Tenant::factory(),
            'slug' => Str::slug($title),
            'title' => Str::title($title),
            'is_homepage' => false,
            'sort_order' => 0,
            'draft_config' => [],
            'published_config' => null,
        ];
    }

    /**
     * Indicate that the page is the homepage.
     */
    public function homepage(): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => 'home',
            'title' => 'Home',
            'is_homepage' => true,
        ]);
    }

    /**
     * Indicate that the page has a published configuration.
     */
    public function published(?array $config = null): static
    {
        $defaultConfig = $config ?: [
            [
                'id' => 'hero-1',
                'type' => 'HeroBlock',
                'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Welcome to your Site', 'subheadline' => 'Built with our engine.'],
                'children' => [],
            ],
        ];

        return $this->state(fn (array $attributes) => [
            'draft_config' => $defaultConfig,
            'published_config' => $defaultConfig,
        ]);
    }
}
