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

test_summary_and_exit();
