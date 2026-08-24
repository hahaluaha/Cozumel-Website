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

// GA4 previously loaded eagerly on every page load, competing with the
// visitor's first tap for main-thread time — Search Console flagged INP
// (interaction responsiveness) as "Need Improvement" on mobile. Only the
// gtag.js fetch + 'config' call (the network/parsing cost) is deferred
// until the first real interaction (scroll, tap, keypress); the 'js'
// timestamp fires immediately so GA4's engagement-time clock still starts
// at actual page load, not at the trigger — otherwise a visitor who reads
// for 15-20s before scrolling would have that reading time undercounted.
// Trade-off: a visitor who leaves without any interaction at all is
// still not counted — accepted, since engaged visits track normally.
function cozumel_ga4_script_tag(string $measurement_id): string {
    $id = esc_js($measurement_id);
    return <<<HTML
<script>
(function () {
  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function () { dataLayer.push(arguments); };
  gtag('js', new Date());
  var loaded = false;
  var events = ['scroll', 'keydown', 'mousemove', 'touchstart', 'click'];
  function loadGA() {
    if (loaded) return;
    loaded = true;
    events.forEach(function (e) { window.removeEventListener(e, loadGA, true); });
    gtag('config', '{$id}');
    var s = document.createElement('script');
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtag/js?id={$id}';
    document.head.appendChild(s);
  }
  events.forEach(function (e) { window.addEventListener(e, loadGA, { passive: true, capture: true }); });
})();
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
        echo cozumel_noindex_meta_tag(is_category('uncategorized') || is_author());
    }, 1);
}
