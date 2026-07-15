<?php

use App\Models\Media;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('authenticated tenant owner can list their media', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    Media::factory()->count(3)->create(['tenant_id' => $tenant->id]);

    $this->actingAs($user);

    $response = $this->getJson("http://{$tenant->subdomain}.domain.localhost/editor/media");

    $response->assertOk();
    $response->assertJsonCount(3);
});

test('authenticated tenant owner can upload a valid image', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->image('hero.jpg', 1920, 1080);

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/media", [
        'file' => $file,
    ]);

    $response->assertStatus(201);
    $response->assertJsonStructure(['id', 'filename', 'url', 'thumb_url']);
    expect($response->json('filename'))->toBe('hero.jpg');
    expect($tenant->media()->count())->toBe(1);
});

test('upload rejects non-image file types', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/media", [
        'file' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['file']);
});

test('upload rejects files exceeding 5MB', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $this->actingAs($user);

    // Create a fake image with a size just over 5MB (5121 KB)
    $file = UploadedFile::fake()->image('big.jpg')->size(5121);

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/media", [
        'file' => $file,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['file']);
});

test('authenticated tenant owner can delete their own media', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $media = Media::factory()->create(['tenant_id' => $tenant->id]);

    // Create a fake file on disk so deletion has something to clean up
    Storage::disk('public')->put($media->path, 'fake content');

    $this->actingAs($user);

    $response = $this->deleteJson("http://{$tenant->subdomain}.domain.localhost/editor/media/{$media->id}");

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
    expect($tenant->media()->count())->toBe(0);
    Storage::disk('public')->assertMissing($media->path);
});

test('tenant cannot delete media belonging to another tenant', function () {
    $userA = User::factory()->create();
    $tenantA = Tenant::factory()->withHomePage()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create();
    $tenantB = Tenant::factory()->withHomePage()->create(['user_id' => $userB->id]);

    $mediaB = Media::factory()->create(['tenant_id' => $tenantB->id]);

    $this->actingAs($userA);

    // UserA tries to delete UserB's media through TenantA's subdomain.
    // The explicit $media->tenant_id !== $tenant->id check returns 403.
    $response = $this->deleteJson("http://{$tenantA->subdomain}.domain.localhost/editor/media/{$mediaB->id}");

    $response->assertStatus(403);
    // Verify TenantB's media is still intact (bypass TenantScope for the assertion)
    $count = Media::withoutGlobalScopes()->where('tenant_id', $tenantB->id)->count();
    expect($count)->toBe(1);

});

test('unauthenticated user cannot access media endpoints', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/media", [
        'file' => UploadedFile::fake()->image('photo.jpg'),
    ]);

    $response->assertStatus(401);
});

test('media list is scoped to the current tenant only', function () {
    $userA = User::factory()->create();
    $tenantA = Tenant::factory()->withHomePage()->create(['user_id' => $userA->id]);

    $userB = User::factory()->create();
    $tenantB = Tenant::factory()->withHomePage()->create(['user_id' => $userB->id]);

    Media::factory()->count(2)->create(['tenant_id' => $tenantA->id]);
    Media::factory()->count(5)->create(['tenant_id' => $tenantB->id]);

    $this->actingAs($userA);

    $response = $this->getJson("http://{$tenantA->subdomain}.domain.localhost/editor/media");

    $response->assertOk();
    $response->assertJsonCount(2);
});
