<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/template-helpers.php';

// Plural by default
assert_equal(cozumel_count_phrase('2', 'guest'), '2 guests', '2 -> plural');
assert_equal(cozumel_count_phrase('0', 'bedroom'), '0 bedrooms', '0 -> plural');
assert_equal(cozumel_count_phrase('6', 'bathroom'), '6 bathrooms', '6 -> plural');

// Singular only at exactly one
assert_equal(cozumel_count_phrase('1', 'guest'), '1 guest', '"1" -> singular');
assert_equal(cozumel_count_phrase(1, 'bedroom'), '1 bedroom', 'int 1 -> singular');
assert_equal(cozumel_count_phrase('1.0', 'bathroom'), '1.0 bathroom', '"1.0" -> singular, value echoed verbatim');

// Fractional counts stay plural and print as given
assert_equal(cozumel_count_phrase('3.5', 'bathroom'), '3.5 bathrooms', '3.5 -> plural, decimal preserved');
assert_equal(cozumel_count_phrase('1.5', 'bathroom'), '1.5 bathrooms', '1.5 -> plural');

// Surrounding whitespace in the stored meta is trimmed
assert_equal(cozumel_count_phrase(' 2 ', 'guest'), '2 guests', 'trims whitespace around the count');

test_summary_and_exit();
