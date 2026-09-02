<?php
// Per-page <title> and <meta name="description"> for the five pages that
// carry the site's search weight: the home page, the /rentals/ hub, and the
// three rental-property pages. WordPress core + GeneratePress emit a <title>
// (just "<Post> - <Site>") but no meta description at all, so search results
// fall back to a scraped snippet. The map below is the single source of
// truth; cozumel_seo_meta_* are pure and unit-tested, the add_filter /
// add_action wiring at the bottom is WordPress glue only.
//
// Keyed by 'home', 'rentals', or a rental-property post slug. The three
// property slugs are the live production slugs (verified against
// cozumelhomes.net/rentals/ on 2026-09-02); if a slug ever drifts, that
// property just falls back to GeneratePress's default title and no meta
// description — home and /rentals/ are unaffected. Titles are deliberately
// standalone (no " | Cozumel Homes" on the property pages) to stay near the
// ~60-char search-result truncation; a couple run to ~64 where the trailing
// keyword earns it. Descriptions target the ~155-char mark. Wording is
// Kelley-approved copy from the 2026-09 keyword pass — do not tweak phrasing
// here without running it past her (it also feeds the Google Business
// Profile text).

function cozumel_seo_meta_map(): array {
    return [
        'home' => [
            'title'       => 'Cozumel Home Rentals, Book Direct | Cozumel Homes',
            'description' => 'Three pet-friendly Cozumel vacation homes — downtown oceanfront, North Shore condo, and a quiet local neighborhood. Book direct with a host on the island since 1997.',
        ],
        'rentals' => [
            'title'       => 'Cozumel Home & House Rentals — Pet-Friendly | Cozumel Homes',
            'description' => 'Compare three Cozumel rental homes — downtown, oceanfront, and residential — with weekly and monthly rates. Pet-friendly, booked direct with a local host.',
        ],
        'cozumels-cool-caribbean-views' => [
            'title'       => 'Cool Caribbean Views — Pet-Friendly Oceanfront Cozumel Apartment',
            'description' => "A pet-friendly oceanfront apartment on Cozumel's downtown malecón — a 12-minute walk to the main plaza and cruise pier. Sleeps 2; nightly, weekly and monthly rates.",
        ],
        'cozumels-nah-ha-condominium-101' => [
            'title'       => 'Nah Ha 101 — Luxury Oceanfront Cozumel Condo Rental',
            'description' => "Luxury oceanfront condo on Cozumel's quiet North Shore — infinity pool, ocean views, sleeps 6, pet-friendly. Weekly and monthly rates, from \$325/night.",
        ],
        'cozumels-casa-bohemia' => [
            'title'       => 'Casa Bohemia — Pet-Friendly House Rental in Cozumel',
            'description' => "A relaxed, pet-friendly house in Corpus Christi, Cozumel — two blocks from the waterfront, short walk to the Playa del Carmen ferry. Sleeps 6, from \$90/night.",
        ],
    ];
}

function cozumel_seo_meta_title(string $key, array $map): string {
    return $map[$key]['title'] ?? '';
}

function cozumel_seo_meta_description(string $key, array $map): string {
    return $map[$key]['description'] ?? '';
}

// Pure: builds the meta tag, or '' when there's no description for the page
// (so callers can echo unconditionally). Escapes " & < > itself rather than
// depending on esc_attr, so it's testable under plain PHP.
function cozumel_meta_description_tag(string $description): string {
    if ($description === '') {
        return '';
    }
    $safe = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    return '<meta name="description" content="' . $safe . '">' . "\n";
}

// Which map key, if any, applies to the page being rendered. Thin WordPress
// glue — kept out of the pure functions above so those stay unit-testable.
function cozumel_current_seo_key(): string {
    if (is_front_page()) {
        return 'home';
    }
    if (is_post_type_archive('rental-property')) {
        return 'rentals';
    }
    if (is_singular('rental-property')) {
        return (string) get_post_field('post_name', get_queried_object_id());
    }
    return '';
}

if (function_exists('add_filter')) {
    // pre_get_document_title short-circuits wp_get_document_title() entirely,
    // so the returned string is the whole <title> and core's own esc_html()
    // (only on the non-short-circuit path) never runs — escape it here, or a
    // literal & in a title reaches <title> as invalid HTML. Only override for
    // the mapped pages; everything else keeps GeneratePress's default.
    add_filter('pre_get_document_title', function ($title) {
        $custom = cozumel_seo_meta_title(cozumel_current_seo_key(), cozumel_seo_meta_map());
        return $custom !== '' ? esc_html($custom) : $title;
    });
}

if (function_exists('add_action')) {
    add_action('wp_head', function () {
        $description = cozumel_seo_meta_description(cozumel_current_seo_key(), cozumel_seo_meta_map());
        echo cozumel_meta_description_tag($description);
    }, 1);
}
