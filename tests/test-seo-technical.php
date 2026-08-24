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

// The 'js' timestamp must fire immediately at the top level (not inside the
// deferred loadGA function), so GA4's engagement-time clock starts at actual
// page load — otherwise a visitor who reads for 15-20s before scrolling/
// tapping would have that reading time undercounted. 'config' (the actual
// network hit) must stay inside loadGA, gated on the first interaction.
$loadGAStart = strpos($tag, 'function loadGA()');
$loadGAEnd = strpos($tag, '}', strpos($tag, 'document.head.appendChild'));
assert_equal($loadGAStart !== false && $loadGAEnd !== false, true, 'loadGA function body is present and locatable');
$loadGABody = substr($tag, $loadGAStart, $loadGAEnd - $loadGAStart);
$beforeLoadGA = substr($tag, 0, $loadGAStart);

assert_equal(strpos($beforeLoadGA, "gtag('js'") !== false, true, "gtag('js', ...) fires immediately, outside the deferred loadGA function");
assert_equal(strpos($loadGABody, "gtag('js'") === false, true, "gtag('js', ...) is not re-fired inside the deferred loadGA function");
assert_equal(strpos($loadGABody, "gtag('config'") !== false, true, "gtag('config', ...) stays deferred inside loadGA, gated on interaction");

test_summary_and_exit();
