<?php
// Pure, framework-free iCal helpers — no WordPress functions used here so
// these are directly testable via `php tests/test-*.php`, no WP bootstrap
// needed. WordPress-facing glue lives in inc/cli-sync-calendars.php.

function cozumel_ical_parse_vevents(string $ics_text): array {
    $events = [];
    $blocks = preg_split('/BEGIN:VEVENT\r?\n/', $ics_text);
    array_shift($blocks); // drop everything before the first VEVENT

    foreach ($blocks as $block) {
        $end_pos = strpos($block, 'END:VEVENT');
        if ($end_pos === false) continue;
        $body = substr($block, 0, $end_pos);

        if (!preg_match('/DTSTART[^:]*:(\d{8})/', $body, $start_match)) continue;
        if (!preg_match('/DTEND[^:]*:(\d{8})/', $body, $end_match)) continue;

        $events[] = [
            'start' => cozumel_ical_date_to_iso($start_match[1]),
            'end'   => cozumel_ical_date_to_iso($end_match[1]),
        ];
    }

    return $events;
}

function cozumel_ical_date_to_iso(string $yyyymmdd): string {
    return substr($yyyymmdd, 0, 4) . '-' . substr($yyyymmdd, 4, 2) . '-' . substr($yyyymmdd, 6, 2);
}

function cozumel_ical_apply_buffer(array $ranges, int $buffer_days): array {
    $buffered = [];
    foreach ($ranges as $range) {
        if (!is_array($range) || empty($range['start']) || empty($range['end'])) {
            continue;
        }
        try {
            $end = new DateTime($range['end']);
        } catch (Exception $e) {
            continue;
        }
        $end->modify("+{$buffer_days} days");
        $buffered[] = ['start' => $range['start'], 'end' => $end->format('Y-m-d')];
    }
    return $buffered;
}

function cozumel_ical_generate(array $ranges, string $calendar_name): string {
    $lines = [
        "BEGIN:VCALENDAR",
        "VERSION:2.0",
        "PRODID:-//Cozumel Homes//Availability Sync//EN",
        "X-WR-CALNAME:{$calendar_name}",
    ];

    foreach ($ranges as $i => $range) {
        $start = str_replace('-', '', $range['start']);
        $end   = str_replace('-', '', $range['end']);
        $lines[] = "BEGIN:VEVENT";
        $lines[] = "UID:cozumel-{$i}-{$start}@cozumelhomes.net";
        $lines[] = "DTSTART;VALUE=DATE:{$start}";
        $lines[] = "DTEND;VALUE=DATE:{$end}";
        $lines[] = "SUMMARY:Unavailable";
        $lines[] = "END:VEVENT";
    }

    $lines[] = "END:VCALENDAR";

    return implode("\r\n", $lines) . "\r\n";
}
