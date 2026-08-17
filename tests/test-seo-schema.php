<?php
require_once __DIR__ . '/test-helpers.php';

// Stub the WordPress functions this pure logic depends on, so the test runs
// with plain `php` and no WordPress bootstrap — same approach the calendar
// sync tests use by keeping WP glue out of the pure functions entirely.
function get_post_meta($post_id, $key, $single = false) {
    global $__test_post_meta;
    return $__test_post_meta[$post_id][$key] ?? '';
}
function get_post_field($field, $post_id) {
    global $__test_post_fields;
    return $__test_post_fields[$post_id][$field] ?? '';
}
function get_the_title($post_id) {
    global $__test_post_titles;
    return $__test_post_titles[$post_id] ?? '';
}
function get_permalink($post_id) {
    return "https://cozumelhomes.net/rentals/test-property-{$post_id}/";
}
function get_the_date($format, $post_id) {
    return '2026-08-16';
}
function get_the_author_meta($field, $user_id = null) {
    global $__test_user_meta;
    if ($user_id === null) {
        return '';
    }
    return $__test_user_meta[$user_id][$field] ?? '';
}
function wp_get_attachment_image_url($attachment_id, $size) {
    return "https://cozumelhomes.net/wp-content/uploads/img-{$attachment_id}.jpg";
}

require_once __DIR__ . '/../theme/cozumel-homes/inc/seo-schema.php';

global $__test_post_meta, $__test_post_titles, $__test_post_fields, $__test_user_meta;
$__test_post_titles[42] = "Cozumel's Nah Ha Condominium 101";
$__test_post_meta[42] = [
    'address'      => 'North Shore Highway Km 3.3',
    'neighborhood' => 'North Shore',
    'base_rate'    => '325',
    'max_guests'   => '6',
    'gallery_ids'  => [101, 102],
];

$schema = cozumel_lodging_business_schema(42);

assert_equal($schema['@context'], 'https://schema.org', 'sets schema.org context');
assert_equal($schema['@type'], 'LodgingBusiness', 'sets LodgingBusiness type');
assert_equal($schema['name'], "Cozumel's Nah Ha Condominium 101", 'uses the post title as name');
assert_equal($schema['url'], 'https://cozumelhomes.net/rentals/test-property-42/', 'uses the permalink as url');
assert_equal($schema['address']['@type'], 'PostalAddress', 'nests a PostalAddress');
assert_equal($schema['address']['streetAddress'], 'North Shore Highway Km 3.3', 'uses the address meta field');
assert_equal($schema['address']['addressLocality'], 'Cozumel', 'hardcodes Cozumel as the locality');
assert_equal($schema['address']['addressRegion'], 'Quintana Roo', 'hardcodes Quintana Roo as the region');
assert_equal($schema['address']['addressCountry'], 'MX', 'hardcodes MX as the country');
assert_equal($schema['priceRange'], '$325', 'formats base_rate as a price range string');
assert_equal(count($schema['image']), 2, 'includes one image URL per gallery_ids entry');
assert_equal($schema['image'][0], 'https://cozumelhomes.net/wp-content/uploads/img-101.jpg', 'first image URL');

// Missing gallery_ids doesn't crash — empty image array instead
$__test_post_meta[43] = ['address' => 'Test St', 'neighborhood' => 'Downtown', 'base_rate' => '180', 'max_guests' => '4'];
$__test_post_titles[43] = 'Test Property No Photos';
$no_photos = cozumel_lodging_business_schema(43);
assert_equal($no_photos['image'], [], 'empty gallery_ids produces an empty image array, not a crash');

// Site-wide LocalBusiness schema
$business = cozumel_local_business_schema();
assert_equal($business['@context'], 'https://schema.org', 'sets schema.org context');
assert_equal($business['@type'], 'LocalBusiness', 'sets LocalBusiness type');
assert_equal($business['name'], 'Cozumel Homes', 'sets the business name');
assert_equal($business['url'], 'https://cozumelhomes.net', 'sets the site URL');
assert_equal($business['address']['addressLocality'], 'Cozumel', 'nests Cozumel as the locality');
assert_equal($business['address']['addressCountry'], 'MX', 'nests MX as the country');

// Article schema for blog posts
$__test_post_titles[44] = 'Meet Your Host: Kelley';
$__test_post_fields[44] = ['post_author' => 5];
$__test_user_meta[5] = ['display_name' => 'Kelley'];
$article = cozumel_article_schema(44);
assert_equal($article['@context'], 'https://schema.org', 'sets schema.org context');
assert_equal($article['@type'], 'Article', 'sets Article type');
assert_equal($article['headline'], 'Meet Your Host: Kelley', 'uses the post title as headline');
assert_equal($article['datePublished'], '2026-08-16', 'uses get_the_date as datePublished');
assert_equal($article['author']['@type'], 'Person', 'nests a Person author');
assert_equal($article['author']['name'], 'Kelley', 'uses get_the_author_meta with post author ID');

// Verify author ID wiring with different user to ensure the ID is actually used
$__test_post_titles[45] = 'Another Blog Post';
$__test_post_fields[45] = ['post_author' => 7];
$__test_user_meta[7] = ['display_name' => 'Fernando'];
$article_2 = cozumel_article_schema(45);
assert_equal($article_2['author']['name'], 'Fernando', 'author ID correctly wires to different user display names');

test_summary_and_exit();
