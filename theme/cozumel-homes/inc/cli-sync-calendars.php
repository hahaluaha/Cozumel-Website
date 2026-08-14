<?php
if (!defined('WP_CLI') || !WP_CLI) {
    return;
}

require_once __DIR__ . '/ical-sync.php';

class Cozumel_Sync_Calendars_Command {
    /**
     * Fetch each rental property's Airbnb iCal feed, store blocked dates,
     * and republish the outbound .ics with the cleaning buffer applied.
     */
    public function __invoke($args, $assoc_args) {
        $buffer_days = 1;
        $properties = get_posts([
            'post_type'      => 'rental-property',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        $failures = [];

        foreach ($properties as $property) {
            $result = $this->sync_one($property, $buffer_days);
            if ($result !== true) {
                $failures[] = "{$property->post_title}: {$result}";
            }
        }

        if (!empty($failures)) {
            $this->email_failures($failures);
            WP_CLI::warning('Completed with ' . count($failures) . ' failure(s): ' . implode('; ', $failures));
        } else {
            WP_CLI::success('Synced ' . count($properties) . ' propert' . (count($properties) === 1 ? 'y' : 'ies') . '.');
        }
    }

    /**
     * @return true|string true on success, error description on failure
     */
    private function sync_one($property, int $buffer_days) {
        $airbnb_url = get_post_meta($property->ID, 'airbnb_ical_url', true);
        if (empty($airbnb_url)) {
            return 'no airbnb_ical_url set, skipped';
        }

        $response = wp_remote_get($airbnb_url, ['timeout' => 20]);
        if (is_wp_error($response)) {
            return 'fetch failed: ' . $response->get_error_message();
        }
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return 'fetch returned HTTP ' . wp_remote_retrieve_response_code($response);
        }

        $body = wp_remote_retrieve_body($response);
        $airbnb_ranges = cozumel_ical_parse_vevents($body);
        if (empty($airbnb_ranges) && strpos($body, 'BEGIN:VCALENDAR') === false) {
            // Not even a valid calendar response — don't overwrite good data
            // with garbage from a malformed/non-iCal response body.
            return 'response was not a valid iCal feed';
        }

        // Inbound: overwrite with fresh data — Airbnb's feed is the full
        // source of truth for this leg, on success only.
        update_post_meta($property->ID, 'airbnb_blocked_dates', wp_json_encode($airbnb_ranges));

        // Outbound: combine Airbnb + manual holds, apply the buffer, publish.
        $manual_raw = get_post_meta($property->ID, 'manual_blocked_dates', true);
        $manual_ranges = json_decode($manual_raw ?: '[]', true);
        if (!is_array($manual_ranges)) {
            $manual_ranges = [];
        }

        $combined = array_merge($airbnb_ranges, $manual_ranges);
        $buffered = cozumel_ical_apply_buffer($combined, $buffer_days);
        $ics = cozumel_ical_generate($buffered, $property->post_title);

        $write_result = $this->write_ics_file($property->post_name, $ics);
        if ($write_result !== true) {
            return $write_result;
        }

        return true;
    }

    /**
     * @return true|string true on success, error description on failure
     */
    private function write_ics_file(string $slug, string $ics_content) {
        $upload_dir = wp_upload_dir();
        $calendars_dir = trailingslashit($upload_dir['basedir']) . 'calendars';

        if (!file_exists($calendars_dir)) {
            if (!wp_mkdir_p($calendars_dir)) {
                return "could not create {$calendars_dir}";
            }
        }

        $path = trailingslashit($calendars_dir) . "{$slug}.ics";
        $tmp_path = "{$path}.tmp";

        // Write to a temp file then rename — an interrupted write must
        // never leave a truncated/empty .ics that would clear Airbnb's
        // import on its next poll.
        if (file_put_contents($tmp_path, $ics_content) === false) {
            return "could not write {$tmp_path}";
        }
        if (!rename($tmp_path, $path)) {
            @unlink($tmp_path);
            return "could not finalize {$path}";
        }

        return true;
    }

    private function email_failures(array $failures): void {
        $to = 'fgmanta@gmail.com';
        $subject = 'Cozumel Homes: calendar sync failure';
        $body = "The calendar sync cron hit " . count($failures) . " failure(s):\n\n"
            . implode("\n", $failures)
            . "\n\nLast-known-good data was preserved for any failed property.";
        wp_mail($to, $subject, $body);
    }
}

WP_CLI::add_command('cozumel sync-calendars', 'Cozumel_Sync_Calendars_Command');
