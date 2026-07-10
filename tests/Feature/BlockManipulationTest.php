<?php

use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->tenant = Tenant::factory()->withHomePage()->create(['user_id' => $this->user->id]);
    $this->page = $this->tenant->pages()->first();

    $this->actingAs($this->user);
});

test('duplicated block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Original', 'subheadline' => ''],
            'children' => [],
        ],
        [
            'id' => 'hero-2',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Duplicate', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('reordered block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'feat-2',
            'type' => 'FeatureBlock',
            'props' => ['padding' => 20, 'backgroundColor' => '#f8fafc', 'title' => 'Second', 'body' => ''],
            'children' => [],
        ],
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'First', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('delete block payload saves successfully', function () {
    $payload = [
        [
            'id' => 'hero-1',
            'type' => 'HeroBlock',
            'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Survivor', 'subheadline' => ''],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('block wrapped in container saves successfully', function () {
    $payload = [
        [
            'id' => 'column-1',
            'type' => 'LayoutColumn',
            'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
            'children' => [
                [
                    'id' => 'hero-1',
                    'type' => 'HeroBlock',
                    'props' => ['padding' => 40, 'backgroundColor' => '#ffffff', 'headline' => 'Wrapped', 'subheadline' => ''],
                    'children' => [],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});

test('duplicated nested block tree saves successfully', function () {
    $payload = [
        [
            'id' => 'grid-1',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 2, 'gap' => '1rem', 'padding' => '1rem', 'backgroundColor' => 'transparent'],
            'children' => [
                [
                    'id' => 'col-1',
                    'type' => 'LayoutColumn',
                    'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
                    'children' => [
                        [
                            'id' => 'text-1',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Hello', 'fontSize' => '16px', 'color' => '#111', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'grid-2',
            'type' => 'LayoutGrid',
            'props' => ['columns' => 2, 'gap' => '1rem', 'padding' => '1rem', 'backgroundColor' => 'transparent'],
            'children' => [
                [
                    'id' => 'col-2',
                    'type' => 'LayoutColumn',
                    'props' => ['padding' => 20, 'backgroundColor' => 'transparent', 'span' => 'auto', 'width' => 'auto', 'height' => 'auto', 'gap' => '0px'],
                    'children' => [
                        [
                            'id' => 'text-2',
                            'type' => 'AtomicText',
                            'props' => ['content' => 'Hello Duplicated', 'fontSize' => '16px', 'color' => '#111', 'padding' => 0, 'backgroundColor' => 'transparent'],
                            'children' => [],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson("http://{$this->tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $this->page->id,
        'draft_config' => $payload,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);
});
