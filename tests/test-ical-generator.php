<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/ical-sync.php';

$ranges = [
    ['start' => '2026-09-01', 'end' => '2026-09-06'],
];

$ics = cozumel_ical_generate($ranges, 'Cozumel Cool Caribbean Views');

assert_equal(strpos($ics, "BEGIN:VCALENDAR\r\n") === 0, true, 'starts with BEGIN:VCALENDAR');
assert_equal(strpos($ics, "END:VCALENDAR\r\n") !== false, true, 'contains END:VCALENDAR');
assert_equal(strpos($ics, "BEGIN:VEVENT\r\n") !== false, true, 'contains a VEVENT');
assert_equal(strpos($ics, "DTSTART;VALUE=DATE:20260901\r\n") !== false, true, 'DTSTART formatted correctly');
assert_equal(strpos($ics, "DTEND;VALUE=DATE:20260906\r\n") !== false, true, 'DTEND formatted correctly');
assert_equal(strpos($ics, "X-WR-CALNAME:Cozumel Cool Caribbean Views\r\n") !== false, true, 'calendar name included');

// Round-trip: what we generate, our own parser can read back
$roundtrip = cozumel_ical_parse_vevents($ics);
assert_equal($roundtrip[0]['start'], '2026-09-01', 'round-trip start matches');
assert_equal($roundtrip[0]['end'], '2026-09-06', 'round-trip end matches');

// Empty ranges still produce a valid (empty) calendar
$empty_ics = cozumel_ical_generate([], 'Empty Property');
assert_equal(strpos($empty_ics, "BEGIN:VCALENDAR\r\n") === 0, true, 'empty ranges still produce valid VCALENDAR wrapper');

test_summary_and_exit();
