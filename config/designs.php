<?php

$heroBlock = static fn (string $id, string $headline, string $subheadline): array => [
    'id' => $id,
    'type' => 'HeroBlock',
    'props' => [
        'padding' => 72,
        'backgroundColor' => 'transparent',
        'headline' => $headline,
        'subheadline' => $subheadline,
    ],
];

$imageBlock = static fn (string $id, string $alt, string $height = '420px'): array => [
    'id' => $id,
    'type' => 'ImageBlock',
    'props' => [
        'src' => '',
        'alt' => $alt,
        'objectFit' => 'cover',
        'borderRadius' => 'var(--theme-border-radius)',
        'width' => '100%',
        'height' => $height,
        'padding' => 0,
        'backgroundColor' => 'transparent',
    ],
];

$headingBlock = static fn (string $id, string $content): array => [
    'id' => $id,
    'type' => 'AtomicText',
    'props' => [
        'padding' => 20,
        'backgroundColor' => 'transparent',
        'content' => $content,
        'fontSize' => '32px',
        'color' => '--theme-text',
    ],
];

$richTextBlock = static fn (string $id, string $html): array => [
    'id' => $id,
    'type' => 'RichTextBlock',
    'props' => [
        'html' => $html,
        'padding' => 20,
        'backgroundColor' => 'transparent',
    ],
];

$featureGrid = static function (string $id, array $features): array {
    return [
        'id' => $id,
        'type' => 'LayoutGrid',
        'props' => [
            'padding' => 20,
            'backgroundColor' => 'transparent',
            'columns' => 3,
            'gap' => '1.5rem',
        ],
        'children' => array_map(
            static fn (array $feature, int $index): array => [
                'id' => "{$id}-item-".($index + 1),
                'type' => 'FeatureBlock',
                'props' => [
                    'padding' => 20,
                    'backgroundColor' => 'transparent',
                    'title' => $feature['title'],
                    'body' => $feature['body'],
                ],
            ],
            $features,
            array_keys($features),
        ),
    ];
};

$buttonBlock = static fn (string $id, string $label, string $url): array => [
    'id' => $id,
    'type' => 'ButtonBlock',
    'props' => [
        'label' => $label,
        'variant' => 'primary',
        'url' => $url,
        'size' => 'lg',
    ],
];

$testimonialBlock = static fn (string $id, string $quote, string $authorName, string $authorRole): array => [
    'id' => $id,
    'type' => 'TestimonialBlock',
    'props' => [
        'quote' => $quote,
        'authorName' => $authorName,
        'authorRole' => $authorRole,
        'avatarSrc' => '',
        'padding' => 40,
        'backgroundColor' => 'transparent',
    ],
];

$contactFormBlock = static fn (string $id, string $submitLabel, string $successMessage, string $messagePlaceholder): array => [
    'id' => $id,
    'type' => 'ContactFormBlock',
    'props' => [
        'fields' => [
            ['type' => 'text', 'label' => 'Your Name', 'placeholder' => 'Full name', 'required' => 'yes'],
            ['type' => 'email', 'label' => 'Your Email', 'placeholder' => 'you@example.com', 'required' => 'yes'],
            ['type' => 'textarea', 'label' => 'Message', 'placeholder' => $messagePlaceholder, 'required' => 'yes'],
        ],
        'submitLabel' => $submitLabel,
        'successMessage' => $successMessage,
        'padding' => 40,
        'backgroundColor' => 'transparent',
    ],
];

return [
    'schema_version' => 1,

    'styles' => [
        'restaurant-warmth' => [
            'label' => 'Restaurant Warmth',
            'theme_config' => [
                'colors' => [
                    'primary' => '#8B3A2B',
                    'secondary' => '#D6A85F',
                    'background' => '#FFF9F2',
                    'text' => '#241A16',
                ],
                'typography' => ['headingFont' => 'Playfair Display', 'bodyFont' => 'Inter'],
                'borderRadius' => '4px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Menu', 'slug' => 'menu'],
                        ['label' => 'About', 'slug' => 'about'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Reserve', 'slug' => 'reservations'],
                ],
                'footer' => ['copyright' => ''],
            ],
        ],
        'retail-editorial' => [
            'label' => 'Retail Editorial',
            'theme_config' => [
                'colors' => [
                    'primary' => '#111827',
                    'secondary' => '#C08457',
                    'background' => '#FAFAF9',
                    'text' => '#1C1917',
                ],
                'typography' => ['headingFont' => 'Outfit', 'bodyFont' => 'Inter'],
                'borderRadius' => '0px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Shop', 'slug' => 'shop'],
                        ['label' => 'About', 'slug' => 'about'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Contact', 'slug' => 'contact'],
                ],
                'footer' => ['copyright' => ''],
            ],
        ],
        'hotel-refined' => [
            'label' => 'Hotel Refined',
            'theme_config' => [
                'colors' => [
                    'primary' => '#153B4A',
                    'secondary' => '#C9A86A',
                    'background' => '#F7F5F0',
                    'text' => '#1E2930',
                ],
                'typography' => ['headingFont' => 'EB Garamond', 'bodyFont' => 'Instrument Sans'],
                'borderRadius' => '4px',
            ],
            'navigation_config' => [
                'header' => [
                    'showLogo' => true,
                    'items' => [
                        ['label' => 'Home', 'slug' => 'home'],
                        ['label' => 'Rooms', 'slug' => 'rooms'],
                        ['label' => 'Amenities', 'slug' => 'amenities'],
                    ],
                    'ctaButton' => ['show' => true, 'label' => 'Book a stay', 'slug' => 'contact'],
                ],
                'footer' => ['copyright' => ''],
            ],
        ],
    ],

    'page_layouts' => [
        'restaurant-home' => [
            'label' => 'Restaurant Home',
            'page_type' => 'home',
            'industry' => 'restaurant',
            'blocks' => [
                $heroBlock('restaurant-home-hero', 'A table worth gathering around', 'Seasonal cooking, generous hospitality, and memorable evenings in the heart of the neighborhood.'),
                $imageBlock('restaurant-home-image', 'Replace with a signature restaurant or dining-room image'),
                $buttonBlock('restaurant-home-menu-button', 'Explore the menu', '/menu'),
                $headingBlock('restaurant-home-highlights-heading', 'What makes the experience special'),
                $featureGrid('restaurant-home-highlights', [
                    ['title' => 'Season-led menus', 'body' => 'Thoughtful dishes built around peak ingredients and trusted local producers.'],
                    ['title' => 'Warm hospitality', 'body' => 'Attentive, unhurried service that makes every table feel welcome.'],
                    ['title' => 'Private gatherings', 'body' => 'Flexible spaces and tailored menus for celebrations, teams, and families.'],
                ]),
                $testimonialBlock('restaurant-home-testimonial', 'Every course felt considered, and the room had exactly the right energy.', 'A recent guest', 'Dinner service'),
            ],
        ],
        'restaurant-menu' => [
            'label' => 'Restaurant Menu',
            'page_type' => 'menu',
            'industry' => 'restaurant',
            'blocks' => [
                $heroBlock('restaurant-menu-hero', 'The menu', 'A concise, seasonal selection designed for sharing, lingering, and discovering something new.'),
                $imageBlock('restaurant-menu-image', 'Replace with a seasonal dish or menu image', '360px'),
                $headingBlock('restaurant-menu-starters-heading', 'To begin'),
                $featureGrid('restaurant-menu-starters', [
                    ['title' => 'Garden plate', 'body' => 'Market vegetables, cultured cream, herbs, and toasted seeds.'],
                    ['title' => 'House bread', 'body' => 'Warm sourdough, whipped butter, and smoked sea salt.'],
                    ['title' => 'Daily crudo', 'body' => 'Fresh catch, citrus, aromatic oil, and a bright seasonal garnish.'],
                ]),
                $headingBlock('restaurant-menu-mains-heading', 'From the kitchen'),
                $featureGrid('restaurant-menu-mains', [
                    ['title' => 'Fire-roasted fish', 'body' => 'Charred greens, lemon, capers, and a light herb sauce.'],
                    ['title' => 'Slow-cooked short rib', 'body' => 'Silky mash, glazed roots, and rich pan jus.'],
                    ['title' => 'Wild mushroom grain bowl', 'body' => 'Ancient grains, woodland mushrooms, greens, and aged cheese.'],
                ]),
            ],
        ],
        'restaurant-about' => [
            'label' => 'Restaurant About',
            'page_type' => 'about',
            'industry' => 'restaurant',
            'blocks' => [
                $heroBlock('restaurant-about-hero', 'Made with care, served with heart', 'Good ingredients, skilled hands, and genuine hospitality under one roof.'),
                $imageBlock('restaurant-about-image', 'Replace with a chef, team, or restaurant story image'),
                $richTextBlock('restaurant-about-story', '<h2>Our story</h2><p>We opened with a simple idea: create a place where the food is thoughtful, the welcome is easy, and guests always have a reason to return. Our menus follow the seasons and our team works closely with producers who care about quality as much as we do.</p><p>From a quick midweek supper to a milestone celebration, every service is built around the people at the table.</p>'),
                $featureGrid('restaurant-about-values', [
                    ['title' => 'Local relationships', 'body' => 'Long-term partnerships with growers, fishers, bakers, and makers.'],
                    ['title' => 'Low-waste thinking', 'body' => 'Whole-ingredient cooking and careful purchasing guide the kitchen.'],
                    ['title' => 'People first', 'body' => 'A supportive team culture translates into better hospitality for every guest.'],
                ]),
            ],
        ],
        'restaurant-reservations' => [
            'label' => 'Restaurant Reservations',
            'page_type' => 'contact',
            'industry' => 'restaurant',
            'blocks' => [
                $heroBlock('restaurant-reservations-hero', 'Reserve your table', 'Send a reservation enquiry and tell us anything we should know before you arrive.'),
                $imageBlock('restaurant-reservations-image', 'Replace with a table setting or private dining image', '320px'),
                $richTextBlock('restaurant-reservations-details', '<p>This form sends an enquiry; it does not confirm live availability. For larger parties, private dining, accessibility requests, or dietary questions, include the details below and the restaurant team can follow up directly.</p>'),
                $contactFormBlock('restaurant-reservations-form', 'Request a reservation', 'Thank you. The restaurant team can now follow up about availability.', 'Preferred date, time, party size, and any special requests'),
            ],
        ],
        'retail-home' => [
            'label' => 'Retail Home',
            'page_type' => 'home',
            'industry' => 'retail',
            'blocks' => [
                $heroBlock('retail-home-hero', 'Objects for a well-considered life', 'A curated collection of useful, beautiful pieces chosen for quality, character, and everyday longevity.'),
                $imageBlock('retail-home-image', 'Replace with a campaign, storefront, or featured collection image'),
                $buttonBlock('retail-home-shop-button', 'Explore the collection', '/shop'),
                $headingBlock('retail-home-collections-heading', 'Featured collections'),
                $featureGrid('retail-home-collections', [
                    ['title' => 'New arrivals', 'body' => 'Fresh seasonal pieces and limited editions, selected in small runs.'],
                    ['title' => 'Everyday essentials', 'body' => 'Dependable staples that earn their place through repeated use.'],
                    ['title' => 'Gifts with character', 'body' => 'Thoughtful finds for hosts, milestones, and just-because moments.'],
                ]),
                $testimonialBlock('retail-home-testimonial', 'The kind of shop where every piece feels chosen rather than simply stocked.', 'A returning customer', 'Local shopper'),
            ],
        ],
        'retail-shop' => [
            'label' => 'Retail Shop',
            'page_type' => 'shop',
            'industry' => 'retail',
            'blocks' => [
                $heroBlock('retail-shop-hero', 'Shop the collection', 'A presentation-ready catalog for home, work, gifting, and the rituals that make up your day.'),
                $imageBlock('retail-shop-image', 'Replace with a collection or product-grid image'),
                $featureGrid('retail-shop-categories', [
                    ['title' => 'Home', 'body' => 'Textiles, tableware, fragrance, and functional objects for everyday rooms.'],
                    ['title' => 'Wear', 'body' => 'Versatile accessories and wardrobe details made to last beyond a season.'],
                    ['title' => 'Gift', 'body' => 'Ready-to-give selections with optional wrapping and personal notes.'],
                ]),
                $richTextBlock('retail-shop-notice', '<p>This starter layout showcases collections and products. It does not include cart, checkout, inventory, or payment functionality.</p>'),
                $buttonBlock('retail-shop-contact-button', 'Ask about an item', '/contact'),
            ],
        ],
        'retail-about' => [
            'label' => 'Retail About',
            'page_type' => 'about',
            'industry' => 'retail',
            'blocks' => [
                $heroBlock('retail-about-hero', 'A slower approach to retail', 'We choose fewer, better things—and share the stories, materials, and people behind them.'),
                $imageBlock('retail-about-image', 'Replace with a founder, studio, or brand story image'),
                $richTextBlock('retail-about-story', '<h2>How we curate</h2><p>Our assortment balances independent makers, established studios, and practical design. We look for honest materials, responsible production, and pieces that become more familiar with time.</p><p>The result is an evolving collection with a clear point of view and room for discovery.</p>'),
                $featureGrid('retail-about-values', [
                    ['title' => 'Purposeful selection', 'body' => 'Every item is considered for usefulness, craft, and lasting appeal.'],
                    ['title' => 'Maker relationships', 'body' => 'We value transparent sourcing and direct, respectful partnerships.'],
                    ['title' => 'Personal service', 'body' => 'Knowledgeable recommendations without pressure or unnecessary noise.'],
                ]),
            ],
        ],
        'retail-contact' => [
            'label' => 'Retail Contact',
            'page_type' => 'contact',
            'industry' => 'retail',
            'blocks' => [
                $heroBlock('retail-contact-hero', 'We are here to help', 'Ask about product details, availability, gifting, local pickup, or an existing order.'),
                $imageBlock('retail-contact-image', 'Replace with a storefront, service desk, or packaging image', '320px'),
                $featureGrid('retail-contact-options', [
                    ['title' => 'Visit the shop', 'body' => 'Add your address, opening hours, parking, and accessibility details here.'],
                    ['title' => 'Product support', 'body' => 'Share the item name so the team can help as quickly as possible.'],
                    ['title' => 'Trade enquiries', 'body' => 'Tell us about sourcing, wholesale, press, or partnership opportunities.'],
                ]),
                $contactFormBlock('retail-contact-form', 'Send enquiry', 'Thanks for getting in touch. The shop team can now reply to your enquiry.', 'How can we help?'),
            ],
        ],
        'hotel-home' => [
            'label' => 'Hotel Home',
            'page_type' => 'home',
            'industry' => 'hotel',
            'blocks' => [
                $heroBlock('hotel-home-hero', 'Arrive, exhale, stay awhile', 'A refined retreat where thoughtful design, attentive service, and a strong sense of place come together.'),
                $imageBlock('hotel-home-image', 'Replace with the hotel exterior, lobby, or destination image'),
                $buttonBlock('hotel-home-rooms-button', 'Explore rooms', '/rooms'),
                $headingBlock('hotel-home-experience-heading', 'Your stay, considered'),
                $featureGrid('hotel-home-experience', [
                    ['title' => 'Restful rooms', 'body' => 'Calm interiors, quality linens, and the details that make settling in effortless.'],
                    ['title' => 'Local character', 'body' => 'Experiences shaped by the landscape, culture, and people around us.'],
                    ['title' => 'Genuine service', 'body' => 'Warm, observant hospitality tailored to the way you prefer to travel.'],
                ]),
                $testimonialBlock('hotel-home-testimonial', 'Beautifully quiet, deeply comfortable, and cared for from the moment we arrived.', 'A recent guest', 'Weekend stay'),
            ],
        ],
        'hotel-rooms' => [
            'label' => 'Hotel Rooms',
            'page_type' => 'rooms',
            'industry' => 'hotel',
            'blocks' => [
                $heroBlock('hotel-rooms-hero', 'Rooms and suites', 'Comfortable, characterful spaces designed for deep rest, slow mornings, and unhurried stays.'),
                $imageBlock('hotel-rooms-image', 'Replace with a guest room or suite image'),
                $featureGrid('hotel-rooms-options', [
                    ['title' => 'Classic room', 'body' => 'An elegant base with a queen bed, walk-in shower, and considered essentials.'],
                    ['title' => 'Terrace room', 'body' => 'More room to unwind, with a private outdoor space and generous natural light.'],
                    ['title' => 'Signature suite', 'body' => 'A separate living area, premium amenities, and the most expansive outlook.'],
                ]),
                $richTextBlock('hotel-rooms-inclusions', '<h2>Included with every stay</h2><p>High-speed Wi-Fi, daily housekeeping, premium bath amenities, in-room refreshments, and support from the guest team throughout your visit.</p>'),
                $buttonBlock('hotel-rooms-book-button', 'Send a stay enquiry', '/contact'),
            ],
        ],
        'hotel-amenities' => [
            'label' => 'Hotel Amenities',
            'page_type' => 'amenities',
            'industry' => 'hotel',
            'blocks' => [
                $heroBlock('hotel-amenities-hero', 'Everything you need, nothing you do not', 'Spaces and services that make each day easier, richer, and distinctly your own.'),
                $imageBlock('hotel-amenities-image', 'Replace with a dining, wellness, pool, or guest-experience image'),
                $featureGrid('hotel-amenities-list', [
                    ['title' => 'Breakfast and dining', 'body' => 'Seasonal menus, locally roasted coffee, and relaxed spaces from morning onward.'],
                    ['title' => 'Wellness', 'body' => 'A calm place to move, recover, and reset during your stay.'],
                    ['title' => 'Work and gatherings', 'body' => 'Flexible rooms with practical support for meetings and private occasions.'],
                    ['title' => 'Concierge', 'body' => 'Local recommendations, reservations, transport, and tailored itineraries.'],
                    ['title' => 'Family stays', 'body' => 'Useful extras and thoughtful arrangements for guests travelling with children.'],
                    ['title' => 'Accessible hospitality', 'body' => 'Clear information and responsive support for individual access requirements.'],
                ]),
            ],
        ],
        'hotel-contact' => [
            'label' => 'Hotel Contact',
            'page_type' => 'contact',
            'industry' => 'hotel',
            'blocks' => [
                $heroBlock('hotel-contact-hero', 'Plan your stay', 'Ask about dates, room recommendations, special occasions, group stays, or anything else the hotel can arrange.'),
                $imageBlock('hotel-contact-image', 'Replace with a destination, arrival, or guest-service image', '320px'),
                $richTextBlock('hotel-contact-details', '<p>This form sends a booking enquiry; it does not check live inventory or confirm a reservation. Share your preferred dates, number of guests, and priorities for the stay so the reservations team can respond with suitable options.</p>'),
                $contactFormBlock('hotel-contact-form', 'Send stay enquiry', 'Thank you. The reservations team can now follow up about your stay.', 'Preferred dates, guest count, room type, and any special requests'),
            ],
        ],
    ],

    'site_kits' => [
        'restaurant' => [
            'label' => 'Restaurant',
            'industry' => 'Food and Dining',
            'description' => 'A warm editorial site for menus, story, hospitality, and reservation enquiries.',
            'style_key' => 'restaurant-warmth',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'restaurant-home', 'is_homepage' => true],
                ['title' => 'Menu', 'slug' => 'menu', 'layout_key' => 'restaurant-menu', 'is_homepage' => false],
                ['title' => 'About', 'slug' => 'about', 'layout_key' => 'restaurant-about', 'is_homepage' => false],
                ['title' => 'Reservations', 'slug' => 'reservations', 'layout_key' => 'restaurant-reservations', 'is_homepage' => false],
            ],
        ],
        'retail' => [
            'label' => 'Retail',
            'industry' => 'Retail and Commerce',
            'description' => 'A clean editorial showcase for collections, brand story, and product enquiries.',
            'style_key' => 'retail-editorial',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'retail-home', 'is_homepage' => true],
                ['title' => 'Shop', 'slug' => 'shop', 'layout_key' => 'retail-shop', 'is_homepage' => false],
                ['title' => 'About', 'slug' => 'about', 'layout_key' => 'retail-about', 'is_homepage' => false],
                ['title' => 'Contact', 'slug' => 'contact', 'layout_key' => 'retail-contact', 'is_homepage' => false],
            ],
        ],
        'hotel' => [
            'label' => 'Hotel',
            'industry' => 'Hospitality and Accommodation',
            'description' => 'A refined hospitality site for rooms, amenities, guest experience, and booking enquiries.',
            'style_key' => 'hotel-refined',
            'pages' => [
                ['title' => 'Home', 'slug' => 'home', 'layout_key' => 'hotel-home', 'is_homepage' => true],
                ['title' => 'Rooms', 'slug' => 'rooms', 'layout_key' => 'hotel-rooms', 'is_homepage' => false],
                ['title' => 'Amenities', 'slug' => 'amenities', 'layout_key' => 'hotel-amenities', 'is_homepage' => false],
                ['title' => 'Contact', 'slug' => 'contact', 'layout_key' => 'hotel-contact', 'is_homepage' => false],
            ],
        ],
    ],
];
