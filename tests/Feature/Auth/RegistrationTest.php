<?php

use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('central.register'));

    $response->assertOk();
});

test('new users can register', function () {
    $response = $this->post(route('central.register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'test-user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect('http://test-user.domain.localhost/editor');
});

test('new users cannot register with a reserved subdomain', function () {
    $response = $this->post(route('central.register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'admin',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['subdomain']);
});

test('new users cannot register with underscores in subdomain', function () {
    $response = $this->post(route('central.register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'subdomain' => 'test_user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors(['subdomain']);
});
