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

// Author archives and the default "Uncategorized" category have no real
// content on this site (single-author, no taxonomy use) — Search Console
// flagged them as crawled-but-unindexed. Noindex keeps them out of the
// index without blocking crawling of the (harmless) pages themselves.
function cozumel_noindex_meta_tag(bool $should_noindex): string {
    return $should_noindex ? '<meta name="robots" content="noindex,follow">' . "\n" : '';
}

function cozumel_ga4_script_tag(string $measurement_id): string {
    $id = esc_js($measurement_id);
    return <<<HTML
<script async src="https://www.googletagmanager.com/gtag/js?id={$id}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$id}');
</script>
HTML;
}

define('COZUMEL_GA4_MEASUREMENT_ID', 'G-T7G2R2D8HR');

if (function_exists('add_filter')) {
    add_filter('robots_txt', 'cozumel_robots_txt_add_sitemap', 10, 1);
}

if (function_exists('add_action')) {
    add_action('wp_head', function () {
        echo cozumel_ga4_script_tag(COZUMEL_GA4_MEASUREMENT_ID);
    });

    add_action('wp_head', function () {
        echo cozumel_noindex_meta_tag(is_category() || is_author());
    }, 1);
}
