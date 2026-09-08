<?php
// Small pure presentation helpers shared by the property templates. Kept
// here rather than inline so they run (and are unit-tested) under plain
// PHP with no WordPress bootstrap.

// "1 guest" / "2 guests" / "3.5 bathrooms" — singular only when the count
// is exactly one. Takes the raw post-meta value (a string like "1" or
// "3.5") and echoes it back verbatim in front of the noun.
function cozumel_count_phrase($count, string $singular_noun): string {
    $noun = ((float) $count === 1.0) ? $singular_noun : $singular_noun . 's';
    return trim((string) $count) . ' ' . $noun;
}

// "2,900 sq ft" from a floor area stored in whole square metres. sq ft is
// the primary unit (US/Canada audience); the metric value stays in the body
// copy and the schema. Rounded to the nearest 50 sq ft so it reads as an
// approximate figure, not a false-precision one. Returns '' for a blank,
// zero, non-numeric, or negative value so the caller can omit it cleanly.
function cozumel_floor_area_phrase($sqm): string {
    // Free-text meta: keep only digits so "269 m²" or "2,900" still convert.
    $sqm = (int) preg_replace('/[^0-9]/', '', (string) $sqm);
    if ($sqm <= 0) {
        return '';
    }
    $sqft = round($sqm * 10.7639 / 50) * 50;
    return number_format($sqft) . ' sq ft';
}
