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

// Malformed ranges are skipped defensively, not thrown — a valid range
// elsewhere in the same input array still processes correctly.
$malformed = cozumel_ical_apply_buffer([
    ['start' => '2026-09-01', 'end' => 'not-a-date'],
    'this-is-not-an-array',
    ['start' => '2026-09-10'], // missing 'end'
    ['start' => '2026-09-15', 'end' => '2026-09-16'],
], 1);
assert_equal(count($malformed), 1, 'malformed ranges are skipped, only the valid one remains');
assert_equal($malformed[0]['start'], '2026-09-15', 'valid range start preserved despite malformed siblings');
assert_equal($malformed[0]['end'], '2026-09-17', 'valid range end still buffered despite malformed siblings');

test_summary_and_exit();
