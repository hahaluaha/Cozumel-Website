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

test_summary_and_exit();
