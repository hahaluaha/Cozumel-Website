<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/seo-meta.php';

$map = cozumel_seo_meta_map();

// Every mapped page has both a non-empty title and description.
foreach (['home', 'rentals', 'cozumels-cool-caribbean-views', 'cozumels-nah-ha-condominium-101', 'cozumels-casa-bohemia'] as $key) {
    assert_equal(cozumel_seo_meta_title($key, $map) !== '', true, "{$key} has a title");
    assert_equal(cozumel_seo_meta_description($key, $map) !== '', true, "{$key} has a description");
}

// Unknown key resolves to empty strings, not a PHP notice / null.
assert_equal(cozumel_seo_meta_title('does-not-exist', $map), '', 'unknown key yields empty title');
assert_equal(cozumel_seo_meta_description('does-not-exist', $map), '', 'unknown key yields empty description');
assert_equal(cozumel_seo_meta_title('', $map), '', 'empty key yields empty title');

// Known content checks — these strings also feed Google Business Profile, so
// pin the load-bearing facts.
assert_equal(
    cozumel_seo_meta_title('home', $map),
    'Cozumel Home Rentals, Book Direct | Cozumel Homes',
    'home title is the keyword-led phrase'
);
assert_equal(
    strpos(cozumel_seo_meta_description('cozumels-casa-bohemia', $map), 'two blocks from the waterfront') !== false,
    true,
    'Casa Bohemia description says two blocks (Kelley 2026-09-02 correction)'
);
assert_equal(
    strpos(cozumel_seo_meta_description('cozumels-cool-caribbean-views', $map), 'apartment') !== false
        && strpos(cozumel_seo_meta_description('cozumels-cool-caribbean-views', $map), 'flat') === false,
    true,
    'Cool Caribbean description uses "apartment", never "flat"'
);
foreach ($map as $key => $entry) {
    assert_equal(
        stripos($entry['description'], 'flat rate') === false && stripos($entry['title'], 'flat rate') === false,
        true,
        "{$key} avoids the phrase \"flat rate\" (implies no other charges)"
    );
}

// Meta tag builder: pure, escapes quotes, no-ops on empty input.
assert_equal(cozumel_meta_description_tag(''), '', 'empty description produces no tag');
assert_equal(
    cozumel_meta_description_tag('Simple copy.'),
    '<meta name="description" content="Simple copy.">' . "\n",
    'wraps a plain description in a meta tag'
);
assert_equal(
    strpos(cozumel_meta_description_tag('He said "hi" & left <b>'), '"hi"') === false,
    true,
    'escapes embedded double quotes so the attribute cannot break out'
);
assert_equal(
    strpos(cozumel_meta_description_tag('a & b'), '&amp;') !== false,
    true,
    'escapes ampersands'
);

test_summary_and_exit();
