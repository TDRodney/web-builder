<?php

use App\Models\ContactSubmission;
use App\Models\Tenant;

test('public user can submit contact form successfully', function () {
    $tenant = Tenant::factory()->create();

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/contact", [
        'page_id' => null,
        'data' => [
            'Name' => 'Alice Smith',
            'Email' => 'alice@example.com',
            'Message' => 'Hello there!',
        ],
    ]);

    $response->assertStatus(201);
    $response->assertJson(['status' => 'success']);

    $this->assertDatabaseHas('contact_submissions', [
        'tenant_id' => $tenant->id,
        'ip_address' => '127.0.0.1',
    ]);

    $submission = ContactSubmission::first();
    expect($submission->form_data)->toBe([
        'Name' => 'Alice Smith',
        'Email' => 'alice@example.com',
        'Message' => 'Hello there!',
    ]);
});

test('contact form submissions are rate limited', function () {
    $tenant = Tenant::factory()->create();

    // Hit the endpoint 5 times successfully
    for ($i = 0; $i < 5; $i++) {
        $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/contact", [
            'data' => ['Message' => "Message {$i}"],
        ]);
        $response->assertStatus(201);
    }

    // The 6th attempt should fail with 429
    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/contact", [
        'data' => ['Message' => 'Too many requests!'],
    ]);

    $response->assertStatus(429);
    $response->assertJsonStructure(['message']);
});
