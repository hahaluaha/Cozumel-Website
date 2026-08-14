<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/ical-sync.php';

$ranges = [
    ['start' => '2026-09-01', 'end' => '2026-09-05'],
    ['start' => '2026-09-20', 'end' => '2026-09-22'],
];

$buffered = cozumel_ical_apply_buffer($ranges, 1);

assert_equal(count($buffered), 2, 'same number of ranges out as in');
assert_equal($buffered[0]['start'], '2026-09-01', 'start date unchanged');
assert_equal($buffered[0]['end'], '2026-09-06', 'end date pushed forward by 1 day');
assert_equal($buffered[1]['end'], '2026-09-23', 'second range end date pushed forward by 1 day');

// Zero buffer is a no-op
$unbuffered = cozumel_ical_apply_buffer($ranges, 0);
assert_equal($unbuffered[0]['end'], '2026-09-05', 'zero buffer leaves end date unchanged');

// Month-boundary case
$boundary = cozumel_ical_apply_buffer([['start' => '2026-09-01', 'end' => '2026-09-30']], 1);
assert_equal($boundary[0]['end'], '2026-10-01', 'buffer correctly rolls over a month boundary');

// Empty input
assert_equal(cozumel_ical_apply_buffer([], 1), [], 'empty input returns empty output');

test_summary_and_exit();
