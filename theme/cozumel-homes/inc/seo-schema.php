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
