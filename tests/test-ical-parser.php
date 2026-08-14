<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/ical-sync.php';

// Two-event fixture, CRLF line endings (real Airbnb feeds use CRLF)
$fixture = "BEGIN:VCALENDAR\r\n" .
    "VERSION:2.0\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:1@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260901\r\n" .
    "DTEND;VALUE=DATE:20260905\r\n" .
    "SUMMARY:Reserved\r\n" .
    "END:VEVENT\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:2@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260920\r\n" .
    "DTEND;VALUE=DATE:20260922\r\n" .
    "SUMMARY:Reserved\r\n" .
    "END:VEVENT\r\n" .
    "END:VCALENDAR\r\n";

$result = cozumel_ical_parse_vevents($fixture);

assert_equal(count($result), 2, 'parses two VEVENT blocks');
assert_equal($result[0]['start'], '2026-09-01', 'first event start date');
assert_equal($result[0]['end'], '2026-09-05', 'first event end date');
assert_equal($result[1]['start'], '2026-09-20', 'second event start date');
assert_equal($result[1]['end'], '2026-09-22', 'second event end date');

// Empty feed
assert_equal(cozumel_ical_parse_vevents("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n"), [], 'empty feed returns empty array');

// Malformed: VEVENT missing DTSTART — should skip silently and parse other events
$malformed_start = "BEGIN:VCALENDAR\r\n" .
    "VERSION:2.0\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:1@airbnb.com\r\n" .
    "DTEND;VALUE=DATE:20260905\r\n" .
    "SUMMARY:Broken\r\n" .
    "END:VEVENT\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:2@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260920\r\n" .
    "DTEND;VALUE=DATE:20260922\r\n" .
    "SUMMARY:Valid\r\n" .
    "END:VEVENT\r\n" .
    "END:VCALENDAR\r\n";
$result_missing_start = cozumel_ical_parse_vevents($malformed_start);
assert_equal(count($result_missing_start), 1, 'skips VEVENT missing DTSTART, parses other events');
assert_equal($result_missing_start[0]['start'], '2026-09-20', 'valid event parsed after malformed');

// Malformed: VEVENT missing DTEND — should skip silently and parse other events
$malformed_end = "BEGIN:VCALENDAR\r\n" .
    "VERSION:2.0\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:1@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260901\r\n" .
    "SUMMARY:Broken\r\n" .
    "END:VEVENT\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:2@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260920\r\n" .
    "DTEND;VALUE=DATE:20260922\r\n" .
    "SUMMARY:Valid\r\n" .
    "END:VEVENT\r\n" .
    "END:VCALENDAR\r\n";
$result_missing_end = cozumel_ical_parse_vevents($malformed_end);
assert_equal(count($result_missing_end), 1, 'skips VEVENT missing DTEND, parses other events');
assert_equal($result_missing_end[0]['end'], '2026-09-22', 'valid event parsed after malformed');

// Malformed: VEVENT block missing END:VEVENT (unterminated) — should skip and parse subsequent well-formed events
$malformed_unterminated = "BEGIN:VCALENDAR\r\n" .
    "VERSION:2.0\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:1@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260901\r\n" .
    "DTEND;VALUE=DATE:20260905\r\n" .
    "SUMMARY:Broken\r\n" .
    "BEGIN:VEVENT\r\n" .
    "UID:2@airbnb.com\r\n" .
    "DTSTART;VALUE=DATE:20260920\r\n" .
    "DTEND;VALUE=DATE:20260922\r\n" .
    "SUMMARY:Valid\r\n" .
    "END:VEVENT\r\n" .
    "END:VCALENDAR\r\n";
$result_unterminated = cozumel_ical_parse_vevents($malformed_unterminated);
assert_equal(count($result_unterminated), 1, 'skips unterminated VEVENT block, parses next well-formed event');
assert_equal($result_unterminated[0]['start'], '2026-09-20', 'well-formed event after unterminated block parsed correctly');

test_summary_and_exit();
