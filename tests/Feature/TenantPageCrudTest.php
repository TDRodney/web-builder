<?php

use App\Models\Page;
use App\Models\Tenant;
use App\Models\User;

test('authenticated tenant owner can list pages', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    // Create an extra page
    $page2 = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'About Us',
        'slug' => 'about',
        'sort_order' => 1,
    ]);

    $this->actingAs($user);

    $response = $this->getJson("http://{$tenant->subdomain}.domain.localhost/editor/pages");

    $response->assertOk();
    $response->assertJsonCount(2);
    $response->assertJsonFragment([
        'slug' => 'home',
        'title' => 'Home',
        'is_homepage' => true,
    ]);
    $response->assertJsonFragment([
        'slug' => 'about',
        'title' => 'About Us',
        'is_homepage' => false,
    ]);
});

test('unauthorized users cannot list pages', function () {
    $user1 = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $this->actingAs($user2);

    $response = $this->getJson("http://{$tenant->subdomain}.domain.localhost/editor/pages");
    $response->assertForbidden();
});

test('tenant owner can create a page with a valid slug and title', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
        'title' => 'Services',
        'slug' => 'services',
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $this->assertDatabaseHas('pages', [
        'tenant_id' => $tenant->id,
        'title' => 'Services',
        'slug' => 'services',
        'is_homepage' => false,
    ]);
});

test('slug validation rules are enforced', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    // Rejects duplicate slug
    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
        'title' => 'Home Duplicate',
        'slug' => 'home',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('slug');

    // Rejects uppercase/spaces/special chars in slug
    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/pages", [
        'title' => 'Invalid Slug',
        'slug' => 'invalid slug!',
    ]);
    $response->assertStatus(422);
    $response->assertJsonValidationErrors('slug');
});

test('different tenants can have the same slug', function () {
    $user1 = User::factory()->create();
    $tenant1 = Tenant::factory()->withHomePage()->create(['user_id' => $user1->id]);

    $user2 = User::factory()->create();
    $tenant2 = Tenant::factory()->withHomePage()->create(['user_id' => $user2->id]);

    $this->actingAs($user1);
    $response = $this->postJson("http://{$tenant1->subdomain}.domain.localhost/editor/pages", [
        'title' => 'Contact Us',
        'slug' => 'contact',
    ]);
    $response->assertOk();

    $this->actingAs($user2);
    $response = $this->postJson("http://{$tenant2->subdomain}.domain.localhost/editor/pages", [
        'title' => 'Contact Us',
        'slug' => 'contact',
    ]);
    $response->assertOk(); // allowed because it's a different tenant!
});

test('tenant owner can update page details', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Old Title',
        'slug' => 'old-slug',
    ]);

    $this->actingAs($user);

    $response = $this->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/pages/{$page->id}", [
        'title' => 'New Title',
        'slug' => 'new-slug',
    ]);

    $response->assertOk();
    $page->refresh();
    expect($page->title)->toBe('New Title')
        ->and($page->slug)->toBe('new-slug');
});

test('setting a page as homepage unsets the previous homepage', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $homePage = $tenant->pages()->where('is_homepage', true)->first();

    $newPage = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'Landing Page',
        'slug' => 'landing',
        'is_homepage' => false,
    ]);

    $this->actingAs($user);

    $response = $this->patchJson("http://{$tenant->subdomain}.domain.localhost/editor/pages/{$newPage->id}", [
        'is_homepage' => true,
    ]);

    $response->assertOk();

    $newPage->refresh();
    $homePage->refresh();

    expect($newPage->is_homepage)->toBeTrue()
        ->and($homePage->is_homepage)->toBeFalse();
});

test('tenant owner can delete a non-homepage page', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $page = Page::factory()->create([
        'tenant_id' => $tenant->id,
        'title' => 'About',
        'slug' => 'about',
    ]);

    $this->actingAs($user);

    $response = $this->deleteJson("http://{$tenant->subdomain}.domain.localhost/editor/pages/{$page->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('pages', ['id' => $page->id]);
});

test('tenant owner cannot delete the homepage', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $homePage = $tenant->pages()->where('is_homepage', true)->first();

    $this->actingAs($user);

    $response = $this->deleteJson("http://{$tenant->subdomain}.domain.localhost/editor/pages/{$homePage->id}");

    $response->assertStatus(422);
    $this->assertDatabaseHas('pages', ['id' => $homePage->id]);
});
