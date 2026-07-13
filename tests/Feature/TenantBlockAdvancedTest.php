<?php

use App\Models\Tenant;
use App\Models\User;

test('authenticated tenant owner can save draft with advanced blocks', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->withHomePage()->create(['user_id' => $user->id]);
    $page = $tenant->pages()->first();

    $this->actingAs($user);

    $draftConfig = [
        [
            'id' => 'rich-1',
            'type' => 'RichTextBlock',
            'props' => [
                'html' => '<h1>Heading 1</h1><p>Paragraph body content</p>',
                'padding' => 20,
            ],
            'children' => [],
        ],
        [
            'id' => 'video-1',
            'type' => 'VideoEmbedBlock',
            'props' => [
                'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'provider' => 'youtube',
                'aspectRatio' => '16/9',
            ],
            'children' => [],
        ],
        [
            'id' => 'faq-1',
            'type' => 'FAQBlock',
            'props' => [
                'items' => [
                    ['question' => 'Q1', 'answer' => 'A1'],
                ],
            ],
            'children' => [],
        ],
        [
            'id' => 'pricing-1',
            'type' => 'PricingTableBlock',
            'props' => [
                'plans' => [
                    ['title' => 'Starter', 'price' => '$9', 'period' => '/mo', 'features' => 'Feat 1, Feat 2', 'ctaText' => 'Buy Now', 'ctaUrl' => '#', 'isPopular' => 'no'],
                ],
            ],
            'children' => [],
        ],
        [
            'id' => 'contact-1',
            'type' => 'ContactFormBlock',
            'props' => [
                'submitLabel' => 'Send',
                'successMessage' => 'Success',
                'fields' => [
                    ['type' => 'text', 'label' => 'Name', 'placeholder' => 'Enter name', 'required' => 'yes'],
                ],
            ],
            'children' => [],
        ],
    ];

    $response = $this->postJson("http://{$tenant->subdomain}.domain.localhost/editor/save", [
        'page_id' => $page->id,
        'draft_config' => $draftConfig,
    ]);

    $response->assertOk();
    $response->assertJson(['status' => 'success']);

    $page->refresh();
    expect($page->draft_config)->toBe($draftConfig);
});
