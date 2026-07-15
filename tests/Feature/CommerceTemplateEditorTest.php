<?php

use App\Models\CommerceTemplate;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('tenant owner can edit a commerce template without entering the page editor', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id, 'subdomain' => 'commerce-editor']);
    $template = CommerceTemplate::factory()->for($tenant)->create();
    $this->actingAs($user)->get(route('tenant.commerce.templates.edit', ['tenant' => $tenant->subdomain, 'commerceTemplate' => $template]))
        ->assertOk()->assertInertia(fn (Assert $page) => $page->component('Tenant/CommerceTemplateEditor')->where('template.id', $template->id)->has('templates', 1)->has('sectionDefinitions', 11));
});

test('owner can save and publish valid commerce sections independently', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $user->id, 'subdomain' => 'commerce-save']);
    $template = CommerceTemplate::factory()->for($tenant)->create();
    $config = ['schemaVersion' => 1, 'sections' => [['id' => 'hero', 'type' => 'image-hero', 'settings' => [], 'blocks' => [], 'disabled' => false]]];
    $this->actingAs($user)->putJson(route('tenant.commerce.templates.update', ['tenant' => $tenant->subdomain, 'commerceTemplate' => $template]), ['draft_config' => $config])->assertOk();
    $this->post(route('tenant.commerce.templates.publish', ['tenant' => $tenant->subdomain, 'commerceTemplate' => $template]))->assertRedirect();
    expect($template->fresh()->published_config)->toBe($config);
});

test('commerce template editor rejects cross tenant access and invalid sections', function () {
    $owner = User::factory()->create();
    $tenant = Tenant::factory()->create(['user_id' => $owner->id, 'subdomain' => 'commerce-protected']);
    $template = CommerceTemplate::factory()->for($tenant)->create();
    $other = User::factory()->create();
    $this->actingAs($other)->get(route('tenant.commerce.templates.edit', ['tenant' => $tenant->subdomain, 'commerceTemplate' => $template]))->assertForbidden();
    $this->actingAs($owner)->putJson(route('tenant.commerce.templates.update', ['tenant' => $tenant->subdomain, 'commerceTemplate' => $template]), ['draft_config' => ['sections' => []]])->assertUnprocessable();
});
