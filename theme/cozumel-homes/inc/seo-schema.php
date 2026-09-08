<?php
// JSON-LD structured data generators. Pure functions — no WordPress calls
// beyond get_post_meta/get_the_title/get_permalink/get_post_field/
// wp_get_attachment_image_url/get_the_date/get_the_author_meta, which the
// test file stubs directly so these run under plain `php`.
//
// Output shape: a single <script type="application/ld+json"> per page holding
// one @graph. Every page carries the LocalBusiness node (@id .../#business);
// rental-property singles add a property node (LodgingBusiness + Apartment,
// @id <permalink>#property) that links back via `provider`, plus a FAQPage
// node when the property has a curated FAQ; blog posts add an Article node.
//
// Per-property richness (geo, floor size, bed count, amenities, ratings,
// verbatim reviews, FAQ) that has no post-meta home lives in
// cozumel_property_schema_data(), keyed by the live production slug — same
// single-source-of-truth pattern as cozumel_seo_meta_map(). Add a slug there
// to extend this to Cool Caribbean Views / Casa Bohemia.

const COZUMEL_BUSINESS_ID = 'https://cozumelhomes.net/#business';

// ── Per-property structured data, keyed by production slug ─────────────────
function cozumel_property_schema_data(string $slug): array {
    $data = [
        'cozumels-nah-ha-condominium-101' => [
            'alternate_name'            => 'Nah Ha 101',
            'postal_code'               => '77613',
            // Deliberate map marker on unit 101 itself (not the Google
            // Business Profile building pin). Used only when the post's own
            // latitude/longitude meta is unset.
            'latitude'                  => '20.535952',
            'longitude'                 => '-86.937937',
            'floor_size_sqm'            => 269,
            'number_of_bedrooms'        => 3,
            'number_of_bathrooms_total' => 3.5,
            'max_occupancy'             => 6,
            'pets_allowed'              => true,
            'same_as'                   => [
                'https://www.google.com/maps?cid=4937929372230107411',
                'https://www.airbnb.com/rooms/1065994830773374907',
            ],
            // No aggregateRating / Review markup: Google's structured-data
            // policy disallows a business publishing rating/review markup about
            // itself, and enforcement can hit the whole domain. The guest
            // reviews live as visible page content instead.
            'amenities'                 => [
                'Oceanfront',
                'Infinity pool',
                'Two jacuzzis',
                'Air conditioning',
                'Dedicated workspace',
                'High-speed fiber internet (850 Mbps)',
                'Full kitchen',
                'Washer',
                'Free street parking',
                'Pet-friendly',
            ],
            'faq' => [
                [
                    'q' => 'Is this the same as the other "Nah Ha" listings online?',
                    'a' => "No. This page is for Nah Ha Condominium 101 specifically — unit 101, ground level, south wing of the Nah Ha building on Cozumel's North Shore — booked directly with Kelley, the owner-host. Other \"Nah Ha\" listings are different units or third-party pages. Every photo and detail on this page is unit 101 itself.",
                ],
                [
                    'q' => 'Where exactly is Nah Ha Condominium 101?',
                    'a' => "On Avenida Rafael E. Melgar at Km 3.5, in the Zona Hotelera Norte on Cozumel's North Shore — about 10 minutes by car north of downtown, or a 25–30 minute walk or cycle along the seafront path.",
                ],
                [
                    'q' => 'How many people does it sleep, and what is the layout?',
                    'a' => 'Up to 6 guests across 3 bedrooms — one king, one queen, and one with two single beds — plus 3.5 bathrooms. The condo is 269 m² (about 2,900 sq ft), on the ground level of the south wing, facing the pool and the ocean.',
                ],
                [
                    'q' => 'Is it a good place for remote work or a monthly stay?',
                    'a' => 'Yes. There is a dedicated desk workspace and Telmex fiber internet at 850 Mbps down / 350 Mbps up. Stays of a month or more use a monthly rate of $5,000, with electricity billed separately by meter reading at CFE rates.',
                ],
                [
                    'q' => 'Is parking available, and are pets allowed?',
                    'a' => 'Guests use free street parking on Avenida Rafael E. Melgar, steps from the entrance. Dogs are welcome with a $50 non-refundable deposit per pet.',
                ],
                [
                    'q' => 'What are the rates?',
                    'a' => 'From $325 per night for up to 2 guests, plus $25 per night for each additional guest (6 guests maximum). Stays of 7 nights or more, up to a month, get 10% off the nightly rate; stays of a month or more use a monthly rate of $5,000 plus metered electricity.',
                ],
            ],
        ],
    ];

    return $data[$slug] ?? [];
}

// ── Nodes (no @context — they live inside the page-level @graph) ──────────
function cozumel_local_business_node(): array {
    return [
        '@type' => 'LocalBusiness',
        '@id'   => COZUMEL_BUSINESS_ID,
        'name'  => 'Cozumel Homes',
        'url'   => 'https://cozumelhomes.net',
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'Avenida Rafael E. Melgar 602, Suite PA-6, Centro',
            'addressLocality' => 'Cozumel',
            'addressRegion'   => 'Quintana Roo',
            'postalCode'      => '77600',
            'addressCountry'  => 'MX',
        ],
    ];
}

function cozumel_property_node(int $post_id): array {
    $slug      = (string) get_post_field('post_name', $post_id);
    $extra     = cozumel_property_schema_data($slug);
    $permalink = get_permalink($post_id);

    $address   = get_post_meta($post_id, 'address', true);
    $base_rate = get_post_meta($post_id, 'base_rate', true);
    $lat       = get_post_meta($post_id, 'latitude', true)  ?: ($extra['latitude'] ?? '');
    $lng       = get_post_meta($post_id, 'longitude', true) ?: ($extra['longitude'] ?? '');
    $airbnb    = get_post_meta($post_id, 'airbnb_listing_url', true);

    $gallery_ids = get_post_meta($post_id, 'gallery_ids', true);
    if (!is_array($gallery_ids)) {
        $gallery_ids = [];
    }
    // wp_get_attachment_image_url() returns false for a missing/deleted
    // attachment id — filter those out so `image` never contains `false`
    // (which happened live: a stale first gallery_ids entry).
    $images = array_values(array_filter(array_map(
        fn($id) => wp_get_attachment_image_url($id, 'large'),
        $gallery_ids
    )));

    $address_node = [
        '@type'           => 'PostalAddress',
        'streetAddress'   => $address,
        'addressLocality' => 'Cozumel',
        'addressRegion'   => 'Quintana Roo',
        'addressCountry'  => 'MX',
    ];
    if (!empty($extra['postal_code'])) {
        $address_node['postalCode'] = $extra['postal_code'];
    }

    // Drop a trailing ".0"/".00" from a "325.0" meta value without rounding a
    // real fractional rate or grouping a 4-digit one ("1200" must not become
    // "1,200").
    $price = $base_rate !== ''
        ? rtrim(rtrim(number_format((float) $base_rate, 2, '.', ''), '0'), '.')
        : '';

    $node = [
        '@type'      => ['LodgingBusiness', 'Apartment'],
        '@id'        => $permalink . '#property',
        'name'       => get_the_title($post_id),
        'url'        => $permalink,
        'address'    => $address_node,
        'priceRange' => $price !== '' ? '$' . $price : '',
        'image'      => $images,
        'provider'   => ['@id' => COZUMEL_BUSINESS_ID],
    ];

    // Everything below is individually guarded, so a property with no per-slug
    // data block still gets geo / sameAs from its own post meta.
    if (!empty($extra['alternate_name'])) {
        $node['alternateName'] = $extra['alternate_name'];
    }
    if ($lat !== '' && $lng !== '') {
        $node['geo'] = [
            '@type'     => 'GeoCoordinates',
            'latitude'  => (string) $lat,
            'longitude' => (string) $lng,
        ];
    }
    if (!empty($extra['number_of_bedrooms'])) {
        $node['numberOfBedrooms'] = $extra['number_of_bedrooms'];
    }
    if (!empty($extra['number_of_bathrooms_total'])) {
        $node['numberOfBathroomsTotal'] = $extra['number_of_bathrooms_total'];
    }
    if (!empty($extra['max_occupancy'])) {
        $node['occupancy'] = [
            '@type'    => 'QuantitativeValue',
            'maxValue' => $extra['max_occupancy'],
            'unitText' => 'guests',
        ];
    }
    if (array_key_exists('pets_allowed', $extra)) {
        $node['petsAllowed'] = (bool) $extra['pets_allowed'];
    }
    if (!empty($extra['floor_size_sqm'])) {
        $node['floorSize'] = [
            '@type'    => 'QuantitativeValue',
            'value'    => $extra['floor_size_sqm'],
            'unitCode' => 'MTK',
        ];
    }
    if (!empty($extra['amenities'])) {
        $node['amenityFeature'] = array_map(
            fn($name) => [
                '@type' => 'LocationFeatureSpecification',
                'name'  => $name,
                'value' => true,
            ],
            $extra['amenities']
        );
    }

    $same_as = $extra['same_as'] ?? [];
    if ($airbnb !== '' && !in_array($airbnb, $same_as, true)) {
        $same_as[] = $airbnb;
    }
    if ($same_as) {
        $node['sameAs'] = array_values($same_as);
    }

    return $node;
}

function cozumel_faq_node(int $post_id): array {
    $slug  = (string) get_post_field('post_name', $post_id);
    $extra = cozumel_property_schema_data($slug);
    if (empty($extra['faq'])) {
        return [];
    }

    return [
        '@type'      => 'FAQPage',
        '@id'        => get_permalink($post_id) . '#faq',
        'mainEntity' => array_map(
            fn($item) => [
                '@type'          => 'Question',
                'name'           => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $item['a'],
                ],
            ],
            $extra['faq']
        ),
    ];
}

function cozumel_article_node(int $post_id): array {
    $author_id = get_post_field('post_author', $post_id);
    return [
        '@type'         => 'Article',
        'headline'      => get_the_title($post_id),
        'datePublished' => get_the_date('Y-m-d', $post_id),
        'author'        => [
            '@type' => 'Person',
            'name'  => get_the_author_meta('display_name', $author_id),
        ],
    ];
}

// ── Page-level @graph assembly + output ───────────────────────────────────
function cozumel_schema_graph(): array {
    $nodes = [cozumel_local_business_node()];

    if (is_singular('rental-property')) {
        $post_id = get_the_ID();
        $nodes[] = cozumel_property_node($post_id);
        $faq = cozumel_faq_node($post_id);
        if ($faq) {
            $nodes[] = $faq;
        }
    }

    if (is_single() && get_post_type() === 'post') {
        $nodes[] = cozumel_article_node(get_the_ID());
    }

    return [
        '@context' => 'https://schema.org',
        '@graph'   => $nodes,
    ];
}

if (function_exists('add_action')) {
    add_action('wp_head', function () {
        echo '<script type="application/ld+json">'
            . wp_json_encode(cozumel_schema_graph())
            . '</script>' . "\n";
    });
}
