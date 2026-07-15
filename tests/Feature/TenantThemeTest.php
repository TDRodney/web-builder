<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Testing\TestResponse;

function themeUpdatePayload(array $overrides = []): array
{
    return array_merge([
        'colors' => [
            'primary' => '#4f46e5',
            'secondary' => '#0ea5e9',
            'background' => '#ffffff',
            'text' => '#0f172a',
        ],
        'typography' => [
            'headingFont' => 'Inter',
            'bodyFont' => 'Inter',
        ],
        'borderRadius' => '8px',
    ], $overrides);
}

function patchTheme(Tenant $tenant, User $user, array $payload, ?User $actingAs = null): TestResponse
{
    return test()->actingAs($actingAs ?? $user)
        ->patchJson("http://{$tenant->subdomain}.domain.localhost/theme", $payload);
}

test('authenticated tenant owner can update theme settings', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $response = patchTheme($tenant, $user, themeUpdatePayload());

    $response->assertOk()
        ->assertJsonPath('status', 'success');

    $tenant->refresh();
    expect($tenant->theme_config['colors']['primary'])->toBe('#4f46e5')
        ->and($tenant->theme_config['typography']['headingFont'])->toBe('Inter')
        ->and($tenant->theme_config['borderRadius'])->toBe('8px');
});

test('unauthorized user cannot update theme settings', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $owner->id]);
    $otherUser = User::factory()->create();

    $response = patchTheme($tenant, $owner, themeUpdatePayload(), $otherUser);

    $response->assertForbidden();
});

test('invalid hex color is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $response = patchTheme($tenant, $user, themeUpdatePayload([
        'colors' => ['primary' => 'not-a-hex'],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['colors.primary']);
});

test('invalid font name is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $response = patchTheme($tenant, $user, themeUpdatePayload([
        'typography' => ['headingFont' => 'Comic Sans'],
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['typography.headingFont']);
});

test('invalid border radius is rejected', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $response = patchTheme($tenant, $user, themeUpdatePayload([
        'borderRadius' => '3px',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['borderRadius']);
});

test('theme update merges with existing settings', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    // Seed with existing settings
    $tenant->update(['theme_config' => [
        'colors' => ['primary' => '#111111', 'secondary' => '#222222', 'background' => '#ffffff', 'text' => '#0f172a'],
        'typography' => ['headingFont' => 'Inter', 'bodyFont' => 'Inter'],
        'borderRadius' => '4px',
    ]]);

    // Partial update: only change colors.primary and borderRadius
    $response = patchTheme($tenant, $user, [
        'colors' => ['primary' => '#4f46e5', 'secondary' => '#0ea5e9', 'background' => '#ffffff', 'text' => '#0f172a'],
        'typography' => ['headingFont' => 'Inter', 'bodyFont' => 'Inter'],
        'borderRadius' => '16px',
    ]);

    $response->assertOk();

    $tenant->refresh();
    // New values persisted
    expect($tenant->theme_config['colors']['primary'])->toBe('#4f46e5')
        ->and($tenant->theme_config['borderRadius'])->toBe('16px');
});
