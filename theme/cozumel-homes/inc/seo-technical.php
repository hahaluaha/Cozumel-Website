<?php
// Technical SEO basics: robots.txt sitemap directive + GA4 tag. Pure
// functions here are directly testable via `php tests/test-*.php`; the
// add_filter/add_action wiring at the bottom is WordPress glue only.

function cozumel_robots_txt_add_sitemap(string $output): string {
    $sitemap_line = 'Sitemap: https://cozumelhomes.net/wp-sitemap.xml';
    if (strpos($output, $sitemap_line) !== false) {
        return $output;
    }
    return rtrim($output) . "\n" . $sitemap_line . "\n";
}

if (function_exists('add_filter')) {
    add_filter('robots_txt', 'cozumel_robots_txt_add_sitemap', 10, 1);
}
