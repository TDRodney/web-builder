<?php

use App\Models\Tenant;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $tenant = Tenant::create([
        'user_id' => $user->id,
        'subdomain' => 'test-tenant',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', ['tenant' => 'test-tenant']));
    $response->assertOk();
});
