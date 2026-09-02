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
