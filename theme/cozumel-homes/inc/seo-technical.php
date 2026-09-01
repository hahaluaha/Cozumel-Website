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

// WordPress core's sitemap lists every individual rental-property /
// forsale-property post but not the post-type archive landing pages
// (/rentals/, /for-sale/). Those archive URLs are the ones meant to rank
// for "cozumel home rentals" and similar, and Search Console had /rentals/
// as "URL is unknown to Google" — nothing in the sitemap pointed at it.
// Core exposes no post-processing filter on a CPT's URL list (only a
// short-circuit), so a dedicated provider publishes them instead:
// wp-sitemap-archives-1.xml, added to the sitemap index. The provider
// name must be [a-z] only — WordPress's sitemap URL rewrite regex does
// not allow hyphens in it.
//
// Pure: given post types and a resolver ('get_post_type_archive_link' in
// production), returns well-formed sitemap entries, skipping any type
// whose archive link can't be resolved.
function cozumel_cpt_archive_sitemap_urls(array $post_types, callable $resolver): array {
    $urls = [];
    foreach ($post_types as $post_type) {
        $link = $resolver($post_type);
        if (is_string($link) && $link !== '') {
            $urls[] = ['loc' => $link];
        }
    }
    return $urls;
}

// Guarded so the test harness (which loads this file without WordPress)
// doesn't fatal on the missing parent class.
if (class_exists('WP_Sitemaps_Provider')) {
    class Cozumel_CPT_Archives_Sitemap_Provider extends WP_Sitemaps_Provider {
        // Single source of truth for the provider name — used here and by
        // the wp_register_sitemap_provider() call. [a-z] only.
        const NAME = 'archives';

        public function __construct() {
            $this->name        = self::NAME;
            $this->object_type = 'archive';
        }

        public function get_url_list($page_num, $object_subtype = '') {
            if ((int) $page_num !== 1) {
                return array();
            }
            return cozumel_cpt_archive_sitemap_urls(
                array('rental-property', 'forsale-property'),
                'get_post_type_archive_link'
            );
        }

        public function get_max_num_pages($object_subtype = '') {
            return 1;
        }

        // Don't advertise wp-sitemap-archives-1.xml in the index if it
        // would render empty (both archive links unresolvable) — an
        // indexed-but-404 sub-sitemap shows as "Couldn't fetch" in GSC.
        public function get_sitemap_entries() {
            if (empty($this->get_url_list(1))) {
                return array();
            }
            return parent::get_sitemap_entries();
        }
    }
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
    add_action('init', function () {
        if (function_exists('wp_register_sitemap_provider') && class_exists('Cozumel_CPT_Archives_Sitemap_Provider')) {
            // Name must match $this->name in the provider and be [a-z] only
            // (WordPress's sitemap URL rewrite regex rejects hyphens).
            wp_register_sitemap_provider(
                Cozumel_CPT_Archives_Sitemap_Provider::NAME,
                new Cozumel_CPT_Archives_Sitemap_Provider()
            );
        }
    });

    add_action('wp_head', function () {
        echo cozumel_ga4_script_tag(COZUMEL_GA4_MEASUREMENT_ID);
    });

    add_action('wp_head', function () {
        echo cozumel_noindex_meta_tag(is_category('uncategorized') || is_author());
    }, 1);
}
