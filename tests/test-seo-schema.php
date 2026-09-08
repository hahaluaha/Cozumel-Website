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
// Mirrors WordPress: returns false for a missing/deleted attachment id.
function wp_get_attachment_image_url($attachment_id, $size) {
    if ($attachment_id === 999) {
        return false;
    }
    return "https://cozumelhomes.net/wp-content/uploads/img-{$attachment_id}.jpg";
}
function get_theme_mod($name, $default = false) {
    global $__test_theme_mods;
    return $__test_theme_mods[$name] ?? $default;
}

require_once __DIR__ . '/../theme/cozumel-homes/inc/seo-schema.php';

global $__test_post_meta, $__test_post_titles, $__test_post_fields, $__test_user_meta;

// ── A rental with a curated per-slug data block (Nah Ha 101) ──────────────
$__test_post_titles[42] = "Cozumel's Nah Ha Condominium 101";
$__test_post_fields[42] = ['post_name' => 'cozumels-nah-ha-condominium-101'];
$__test_post_meta[42] = [
    'address'      => 'Av. Rafael E. Melgar, Km 3.5, Zona Hotelera Norte',
    'neighborhood' => 'North Shore',
    'base_rate'    => '325.0',                // stored with a trailing .0 live
    'max_guests'   => '6',
    'gallery_ids'  => [999, 101, 102],        // 999 = stale/deleted attachment
];

$schema = cozumel_property_node(42);

assert_equal($schema['@type'], ['LodgingBusiness', 'Apartment'], 'property node is typed as both LodgingBusiness and Apartment');
assert_equal($schema['@id'], 'https://cozumelhomes.net/rentals/test-property-42/#property', 'property node has a stable @id');
assert_equal($schema['name'], "Cozumel's Nah Ha Condominium 101", 'uses the post title as name');
assert_equal($schema['alternateName'], 'Nah Ha 101', 'carries the GBP name as alternateName');
assert_equal($schema['url'], 'https://cozumelhomes.net/rentals/test-property-42/', 'uses the permalink as url');
assert_equal($schema['address']['@type'], 'PostalAddress', 'nests a PostalAddress');
assert_equal($schema['address']['streetAddress'], 'Av. Rafael E. Melgar, Km 3.5, Zona Hotelera Norte', 'uses the address meta field');
assert_equal($schema['address']['postalCode'], '77613', 'adds the property postal code from the per-slug data');
assert_equal($schema['address']['addressLocality'], 'Cozumel', 'hardcodes Cozumel as the locality');
assert_equal($schema['address']['addressRegion'], 'Quintana Roo', 'hardcodes Quintana Roo as the region');
assert_equal($schema['address']['addressCountry'], 'MX', 'hardcodes MX as the country');
assert_equal($schema['priceRange'], '$325', 'formats a "325.0" base_rate as "$325", not "$325.0"');
assert_equal($schema['parentOrganization']['@id'], 'https://cozumelhomes.net/#business', 'links to the business node via parentOrganization (domain-valid, unlike provider)');
assert_equal(isset($schema['provider']), false, 'no invalid provider property on the lodging node');

// priceRange: strip trailing zeros without rounding or thousands-grouping,
// and tolerate a currency symbol / separator in the free-text meta
$__test_post_meta[42]['base_rate'] = '325.50';
assert_equal(cozumel_property_node(42)['priceRange'], '$325.5', 'a real fractional rate is not rounded away');
$__test_post_meta[42]['base_rate'] = '1200';
assert_equal(cozumel_property_node(42)['priceRange'], '$1200', 'a 4-digit rate gets no thousands separator');
$__test_post_meta[42]['base_rate'] = '1,200';
assert_equal(cozumel_property_node(42)['priceRange'], '$1200', 'a comma in the stored rate does not collapse it to "$1"');
$__test_post_meta[42]['base_rate'] = '$325';
assert_equal(cozumel_property_node(42)['priceRange'], '$325', 'a leading currency symbol in the stored rate is tolerated');
$__test_post_meta[42]['base_rate'] = '325.0';

// image[] must never contain a literal false (the live bug)
assert_equal(count($schema['image']), 2, 'drops the unresolvable attachment id from image[]');
assert_equal($schema['image'][0], 'https://cozumelhomes.net/wp-content/uploads/img-101.jpg', 'image[] is reindexed from 0 after filtering');
assert_equal(in_array(false, $schema['image'], true), false, 'image[] contains no false entries');

// geo from the per-slug marker
assert_equal($schema['geo']['@type'], 'GeoCoordinates', 'emits GeoCoordinates');
assert_equal($schema['geo']['latitude'], '20.535952', 'geo latitude from the unit-101 marker');
assert_equal($schema['geo']['longitude'], '-86.937937', 'geo longitude from the unit-101 marker');

// rooms / size / occupancy / pets
assert_equal($schema['numberOfBedrooms'], 3, 'sets numberOfBedrooms');
assert_equal($schema['numberOfBathroomsTotal'], 3.5, 'sets numberOfBathroomsTotal');
assert_equal($schema['occupancy']['maxValue'], 6, 'sets max occupancy');
assert_equal($schema['petsAllowed'], true, 'sets petsAllowed');
assert_equal($schema['floorSize']['value'], 269, 'floorSize falls back to the per-slug m² when no meta is set');
assert_equal($schema['floorSize']['unitCode'], 'MTK', 'floorSize uses the UN/CEFACT square-metre code');

// floor_size_sqm post meta overrides the per-slug fallback
$__test_post_meta[42]['floor_size_sqm'] = '300';
assert_equal(cozumel_property_node(42)['floorSize']['value'], 300, 'floor_size_sqm meta overrides the per-slug m²');
assert_equal(is_int(cozumel_property_node(42)['floorSize']['value']), true, 'a whole-number m² meta serialises as a number, not a string');
// free-text meta: unit suffix / thousands separator must not warn or truncate
$__test_post_meta[42]['floor_size_sqm'] = '269 m²';
assert_equal(cozumel_property_node(42)['floorSize']['value'], 269, 'a "269 m²" meta reduces to the integer 269');
$__test_post_meta[42]['floor_size_sqm'] = '2,900';
assert_equal(cozumel_property_node(42)['floorSize']['value'], 2900, 'a "2,900" meta reduces to 2900, not 2');
$__test_post_meta[42]['floor_size_sqm'] = 'n/a';
assert_equal(isset(cozumel_property_node(42)['floorSize']), false, 'a non-numeric m² meta falls through to no floorSize');
unset($__test_post_meta[42]['floor_size_sqm']);

// amenities
assert_equal($schema['amenityFeature'][0]['@type'], 'LocationFeatureSpecification', 'amenities are LocationFeatureSpecification nodes');
assert_equal($schema['amenityFeature'][0]['name'], 'Oceanfront', 'first amenity name');
assert_equal($schema['amenityFeature'][0]['value'], true, 'amenity value is boolean true');

// sameAs — GBP + Airbnb, with the meta URL appended if distinct
assert_equal($schema['sameAs'][0], 'https://www.google.com/maps?cid=4937929372230107411', 'sameAs includes the GBP CID URL');
assert_equal($schema['sameAs'][1], 'https://www.airbnb.com/rooms/1065994830773374907', 'sameAs includes the Airbnb URL');

// No self-serving review markup — Google policy. Guest reviews are visible
// page content only, never aggregateRating / Review nodes.
assert_equal(isset($schema['aggregateRating']), false, 'no self-serving aggregateRating in the schema');
assert_equal(isset($schema['review']), false, 'no self-serving Review nodes in the schema');

// latitude/longitude post meta wins over the per-slug marker when present
$__test_post_meta[42]['latitude']  = '20.500000';
$__test_post_meta[42]['longitude'] = '-86.900000';
$with_meta_geo = cozumel_property_node(42);
assert_equal($with_meta_geo['geo']['latitude'], '20.500000', 'post latitude meta overrides the per-slug marker');
assert_equal($with_meta_geo['geo']['longitude'], '-86.900000', 'post longitude meta overrides the per-slug marker');
unset($__test_post_meta[42]['latitude'], $__test_post_meta[42]['longitude']);

// airbnb_listing_url meta is appended to sameAs when it isn't already there
$__test_post_meta[42]['airbnb_listing_url'] = 'https://www.airbnb.com/rooms/OTHER';
$with_airbnb = cozumel_property_node(42);
assert_equal(in_array('https://www.airbnb.com/rooms/OTHER', $with_airbnb['sameAs'], true), true, 'a distinct airbnb_listing_url meta is added to sameAs');
unset($__test_post_meta[42]['airbnb_listing_url']);

// ── FAQ node ─────────────────────────────────────────────────────────────
$faq = cozumel_faq_node(42);
assert_equal($faq['@type'], 'FAQPage', 'FAQ node is a FAQPage');
assert_equal($faq['@id'], 'https://cozumelhomes.net/rentals/test-property-42/#faq', 'FAQ node has a stable @id');
assert_equal(count($faq['mainEntity']), 6, 'FAQ node has all six questions');
assert_equal($faq['mainEntity'][0]['@type'], 'Question', 'FAQ entries are Question nodes');
assert_equal($faq['mainEntity'][0]['name'], 'Is this the same as the other "Nah Ha" listings online?', 'FAQ text passes through untouched when wptexturize is unavailable');
assert_equal($faq['mainEntity'][0]['acceptedAnswer']['@type'], 'Answer', 'FAQ answers are Answer nodes');

// When wptexturize exists (it always does in wp_head), the schema Q&A is run
// through it so it matches the texturized visible FAQ character-for-character.
// Defined inside a block so it is NOT hoisted above the assertion above.
if (!function_exists('wptexturize')) {
    function wptexturize($s) { return str_replace('"', "\u{201C}", $s); }
}
$faq_tx = cozumel_faq_node(42);
assert_equal(strpos($faq_tx['mainEntity'][0]['name'], '"'), false, 'straight quotes are gone from the schema FAQ once texturized');
assert_equal(strpos($faq_tx['mainEntity'][0]['name'], "\u{201C}") !== false, true, 'schema FAQ carries the same curly quote the page will render');

// ── A rental with NO per-slug data block (e.g. before CCV/Bohemia added) ──
$__test_post_meta[43] = ['address' => 'Test St', 'neighborhood' => 'Downtown', 'base_rate' => '180', 'max_guests' => '4'];
$__test_post_titles[43] = 'Test Property No Photos';
$__test_post_fields[43] = ['post_name' => 'some-other-rental'];
$plain = cozumel_property_node(43);
assert_equal($plain['image'], [], 'empty gallery_ids produces an empty image array, not a crash');
assert_equal($plain['@type'], ['LodgingBusiness', 'Apartment'], 'still typed as LodgingBusiness + Apartment without a data block');
assert_equal($plain['priceRange'], '$180', 'price range still formatted without a data block');
assert_equal(isset($plain['geo']), false, 'no geo when there is no per-slug data and no lat/long meta');
assert_equal(isset($plain['aggregateRating']), false, 'no ratings invented without a data block');
assert_equal(isset($plain['address']['postalCode']), false, 'no postal code when the data block does not supply one');
assert_equal(isset($plain['floorSize']), false, 'no floorSize without a data block or floor_size_sqm meta');
assert_equal(cozumel_faq_node(43), [], 'no FAQ node for a property without curated FAQ');

// ...but a property with no data block STILL emits geo + sameAs + floorSize
// from its own post meta (the case CCV / Casa Bohemia will hit first).
$__test_post_meta[43]['latitude']  = '20.511111';
$__test_post_meta[43]['longitude'] = '-86.911111';
$__test_post_meta[43]['airbnb_listing_url'] = 'https://www.airbnb.com/rooms/CCV';
$__test_post_meta[43]['floor_size_sqm'] = '95';
$plain_with_meta = cozumel_property_node(43);
assert_equal($plain_with_meta['geo']['latitude'], '20.511111', 'geo comes from post meta even without a per-slug data block');
assert_equal($plain_with_meta['sameAs'], ['https://www.airbnb.com/rooms/CCV'], 'sameAs comes from airbnb_listing_url meta even without a data block');
assert_equal($plain_with_meta['floorSize']['value'], 95, 'floorSize comes from floor_size_sqm meta even without a data block');
unset($__test_post_meta[43]['latitude'], $__test_post_meta[43]['longitude'], $__test_post_meta[43]['airbnb_listing_url']);

// ── Site-wide LocalBusiness node ─────────────────────────────────────────
$business = cozumel_local_business_node();
assert_equal($business['@type'], 'LocalBusiness', 'sets LocalBusiness type');
assert_equal($business['@id'], 'https://cozumelhomes.net/#business', 'business node has the stable @id the property links to');
assert_equal($business['name'], 'Cozumel Homes', 'sets the business name');
assert_equal($business['url'], 'https://cozumelhomes.net', 'sets the site URL');
assert_equal($business['telephone'], '+52 987 876 0638', 'business node carries the contact phone (NAP consistency)');
assert_equal($business['priceRange'], '$75–$325', 'business node carries a price range');
assert_equal($business['logo'], COZUMEL_BUSINESS_LOGO_FALLBACK, 'logo falls back to the constant when no Customizer logo is set');
assert_equal($business['image'], $business['logo'], 'image mirrors the logo so the Rich Results optional field is satisfied');

// ...and resolves from the Customizer custom_logo attachment once one is set,
// so it survives a media rename/replace.
$__test_theme_mods['custom_logo'] = 155;
$biz_with_logo = cozumel_local_business_node();
assert_equal($biz_with_logo['logo'], 'https://cozumelhomes.net/wp-content/uploads/img-155.jpg', 'logo comes from the Customizer custom_logo when set');
assert_equal($biz_with_logo['image'], $biz_with_logo['logo'], 'image tracks the resolved logo');
unset($__test_theme_mods['custom_logo']);
assert_equal($business['address']['streetAddress'], 'Avenida Rafael E. Melgar 602, Suite PA-6, Centro', 'business node now carries a real streetAddress');
assert_equal($business['address']['postalCode'], '77600', 'business postal code is the Centro one, distinct from the property');
assert_equal($business['address']['addressLocality'], 'Cozumel', 'nests Cozumel as the locality');
assert_equal($business['address']['addressCountry'], 'MX', 'nests MX as the country');

// ── Article node for blog posts ─────────────────────────────────────────
$__test_post_titles[44] = 'Meet Your Host: Kelley';
$__test_post_fields[44] = ['post_author' => 5];
$__test_user_meta[5] = ['display_name' => 'Kelley'];
$article = cozumel_article_node(44);
assert_equal($article['@type'], 'Article', 'sets Article type');
assert_equal($article['headline'], 'Meet Your Host: Kelley', 'uses the post title as headline');
assert_equal($article['datePublished'], '2026-08-16', 'uses get_the_date as datePublished');
assert_equal($article['author']['@type'], 'Person', 'nests a Person author');
assert_equal($article['author']['name'], 'Kelley', 'uses get_the_author_meta with post author ID');

$__test_post_titles[45] = 'Another Blog Post';
$__test_post_fields[45] = ['post_author' => 7];
$__test_user_meta[7] = ['display_name' => 'Fernando'];
$article_2 = cozumel_article_node(45);
assert_equal($article_2['author']['name'], 'Fernando', 'author ID correctly wires to different user display names');

// ── @graph assembly ─────────────────────────────────────────────────────
// Minimal stubs for the conditional tags cozumel_schema_graph() reads.
$__test_singular_rental = false;
$__test_single_post = false;
function is_singular($type) { global $__test_singular_rental; return $type === 'rental-property' && $__test_singular_rental; }
function is_single() { global $__test_single_post; return $__test_single_post; }
function get_post_type() { return 'post'; }
function get_the_ID() { return 42; }

$graph = cozumel_schema_graph();
assert_equal($graph['@context'], 'https://schema.org', 'graph has a single top-level @context');
assert_equal(count($graph['@graph']), 1, 'off a rental single, the graph is just the business node');
assert_equal($graph['@graph'][0]['@id'], 'https://cozumelhomes.net/#business', 'business node is always present');

$__test_singular_rental = true;
$graph_rental = cozumel_schema_graph();
assert_equal(count($graph_rental['@graph']), 3, 'on a rental single: business + property + FAQ nodes');
assert_equal($graph_rental['@graph'][1]['@id'], 'https://cozumelhomes.net/rentals/test-property-42/#property', 'second node is the property');
assert_equal($graph_rental['@graph'][2]['@type'], 'FAQPage', 'third node is the FAQ');

test_summary_and_exit();
