<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/seo-technical.php';

// Appends the sitemap line to existing robots.txt output
$base = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
$result = cozumel_robots_txt_add_sitemap($base);
assert_equal(
    strpos($result, "Sitemap: https://cozumelhomes.net/wp-sitemap.xml") !== false,
    true,
    'appends sitemap directive'
);
assert_equal(
    strpos($result, "Disallow: /wp-admin/") !== false,
    true,
    'preserves existing robots.txt content'
);

// Does not duplicate the line if called twice
$twice = cozumel_robots_txt_add_sitemap($result);
assert_equal(
    substr_count($twice, 'Sitemap: https://cozumelhomes.net/wp-sitemap.xml'),
    1,
    'does not duplicate the sitemap directive'
);

// Noindex meta tag for author/category archives
assert_equal(
    cozumel_noindex_meta_tag(true),
    '<meta name="robots" content="noindex,follow">' . "\n",
    'emits noindex,follow meta tag when should_noindex is true'
);
assert_equal(cozumel_noindex_meta_tag(false), '', 'emits nothing when should_noindex is false');

// GA4 script tag — deferred until first interaction, not loaded eagerly
$tag = cozumel_ga4_script_tag('G-TEST12345');
assert_equal(strpos($tag, "gtag/js?id=G-TEST12345") !== false, true, 'loads gtag.js with the measurement ID');
assert_equal(strpos($tag, "gtag('config', 'G-TEST12345')") !== false, true, 'configures gtag with the measurement ID');
assert_equal(strpos($tag, '<script') !== false, true, 'wraps output in a script tag');
assert_equal(
    strpos($tag, 'src="https://www.googletagmanager.com') === false,
    true,
    'does not eagerly load gtag.js via a static <script src> tag'
);
assert_equal(strpos($tag, "'scroll'") !== false && strpos($tag, "'click'") !== false, true, 'listens for scroll and click as interaction triggers');
assert_equal(strpos($tag, 'addEventListener') !== false, true, 'defers loading until an interaction event fires');

test_summary_and_exit();
