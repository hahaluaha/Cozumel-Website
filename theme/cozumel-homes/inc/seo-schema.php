<?php
// JSON-LD structured data generators. Pure functions — no WordPress calls
// beyond get_post_meta/get_the_title/get_permalink/wp_get_attachment_image_url,
// which the test file stubs directly so these run under plain `php`.

function cozumel_lodging_business_schema(int $post_id): array {
    $address = get_post_meta($post_id, 'address', true);
    $base_rate = get_post_meta($post_id, 'base_rate', true);
    $gallery_ids = get_post_meta($post_id, 'gallery_ids', true);
    if (!is_array($gallery_ids)) {
        $gallery_ids = [];
    }

    $images = array_map(
        fn($id) => wp_get_attachment_image_url($id, 'large'),
        $gallery_ids
    );

    return [
        '@context' => 'https://schema.org',
        '@type'    => 'LodgingBusiness',
        'name'     => get_the_title($post_id),
        'url'      => get_permalink($post_id),
        'address'  => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => $address,
            'addressLocality' => 'Cozumel',
            'addressRegion'   => 'Quintana Roo',
            'addressCountry'  => 'MX',
        ],
        'priceRange' => $base_rate !== '' ? '$' . $base_rate : '',
        'image'      => $images,
    ];
}

function cozumel_local_business_schema(): array {
    return [
        '@context' => 'https://schema.org',
        '@type'    => 'LocalBusiness',
        'name'     => 'Cozumel Homes',
        'url'      => 'https://cozumelhomes.net',
        'address'  => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Cozumel',
            'addressRegion'   => 'Quintana Roo',
            'addressCountry'  => 'MX',
        ],
    ];
}

if (function_exists('add_action')) {
    add_action('wp_head', function () {
        if (is_singular('rental-property')) {
            $schema = cozumel_lodging_business_schema(get_the_ID());
            echo '<script type="application/ld+json">' . wp_json_encode($schema) . '</script>' . "\n";
        }

        $business_schema = cozumel_local_business_schema();
        echo '<script type="application/ld+json">' . wp_json_encode($business_schema) . '</script>' . "\n";
    });
}
