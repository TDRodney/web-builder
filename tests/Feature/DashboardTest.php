<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $themeConfig = [
        'colors' => [
            'primary' => '#4f46e5',
            'secondary' => '#0ea5e9',
            'background' => '#ffffff',
            'text' => '#0f172a',
        ],
        'typography' => [
            'headingFont' => 'Instrument Sans',
            'bodyFont' => 'Inter',
        ],
        'borderRadius' => '8px',
    ];
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
        'theme_config' => $themeConfig,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));

    $response
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('CentralDashboard')
            ->where('tenant.id', $tenant->id)
            ->where('tenant.subdomain', 'test-tenant')
            ->where('theme_config', $themeConfig)
        );
});
