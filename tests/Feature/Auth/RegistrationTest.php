<?php

use App\Models\Tenant;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'test-user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', ['tenant' => 'test-user']));

    $tenant = Tenant::query()->where('subdomain', 'test-user')->firstOrFail();

    expect($tenant->site_setup_completed_at)->toBeNull()
        ->and($tenant->theme_config)->toBeNull()
        ->and($tenant->navigation_config)->toBeNull()
        ->and($tenant->pages()->count())->toBe(0)
        ->and($tenant->isEligibleForInitialSiteKit())->toBeTrue();
});

test('new users cannot register with a reserved subdomain', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'admin',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['subdomain']);
});

test('new users cannot register with underscores in subdomain', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'test_user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['subdomain']);
});
