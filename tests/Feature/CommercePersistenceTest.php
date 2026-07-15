<?php

use App\Models\CommerceConnection;
use App\Models\CommerceTemplate;
use App\Models\Tenant;
use App\Rules\ValidatesCommerceTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

test('commerce credentials are encrypted and related to one tenant', function () {
    $connection = CommerceConnection::factory()->create(['credentials' => ['token' => 'secret-token']]);
    expect(DB::table('commerce_connections')->find($connection->id)->credentials)->not->toContain('secret-token')
        ->and($connection->fresh()->credentials)->toBe(['token' => 'secret-token']);
});

test('commerce templates preserve independent draft and published configurations', function () {
    $template = CommerceTemplate::factory()->create([
        'draft_config' => ['schemaVersion' => 1, 'sections' => [['id' => 'hero', 'type' => 'image-hero', 'settings' => [], 'blocks' => [], 'disabled' => false]]],
        'published_config' => ['schemaVersion' => 1, 'sections' => []],
    ]);
    expect($template->draft_config['sections'])->toHaveCount(1)->and($template->published_config['sections'])->toBeEmpty();
});

test('commerce templates are tenant scoped without changing pages', function () {
    $tenant = Tenant::factory()->create();
    CommerceTemplate::factory()->for($tenant)->create();
    CommerceTemplate::factory()->create(['key' => 'alternate']);
    app()->instance('currentTenant', $tenant);
    expect(CommerceTemplate::query()->count())->toBe(1)->and($tenant->commerceTemplates()->count())->toBe(1);
});

test('commerce template structure is validated', function () {
    $section = ['id' => 'hero', 'type' => 'image-hero', 'settings' => [], 'blocks' => [], 'disabled' => false];
    $valid = ['schemaVersion' => 1, 'sections' => [$section]];
    $duplicate = ['schemaVersion' => 1, 'sections' => [$section, $section]];
    expect(Validator::make(['template' => $valid], ['template' => new ValidatesCommerceTemplate])->passes())->toBeTrue()
        ->and(Validator::make(['template' => $duplicate], ['template' => new ValidatesCommerceTemplate])->passes())->toBeFalse();
});
