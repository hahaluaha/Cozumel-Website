<?php
function cozumel_render_inquiry_form($property_name = '', $availability = null) {
    if (isset($_GET['inquiry']) && $_GET['inquiry'] === 'sent') {
        echo '<p style="color:#2a6fa8;font-weight:600">Thanks — your message has been sent. We\'ll get back to you soon.</p>';
    } elseif (isset($_GET['inquiry']) && $_GET['inquiry'] === 'error') {
        echo '<p style="color:#b23b3b;font-weight:600">Something went wrong sending your message. Please try again or email us directly at home@cozumelhomes.net.</p>';
    }
    ?>
    <form class="inquiry-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="cozumel_inquiry">
        <?php wp_nonce_field('cozumel_inquiry', 'cozumel_inquiry_nonce'); ?>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url(is_front_page() ? home_url('/') : (get_permalink() ?: home_url('/'))); ?>">
        <p style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </p>

        <?php if ($availability): ?>
            <div class="cozumel-date-fields"
                 data-cozumel-availability-calendar
                 data-property-id="<?php echo esc_attr($availability['property_id']); ?>"
                 data-api-url="<?php echo esc_url($availability['api_url']); ?>">
                <p><label>Preferred Check-in Date<br>
                    <button type="button" class="date-trigger" data-role="checkin">Select date</button>
                </label></p>
                <p><label>Preferred Check-out Date<br>
                    <button type="button" class="date-trigger" data-role="checkout" disabled>Select date</button>
                </label></p>
                <input type="hidden" name="checkin_date">
                <input type="hidden" name="checkout_date">
                <div class="availability-calendar-popover is-hidden"></div>
            </div>
            <div class="inquiry-form__rest is-hidden">
        <?php endif; ?>

        <p><label>Your Name<br><input type="text" name="your_name" required></label></p>
        <p><label>Email Address<br><input type="email" name="your_email" required></label></p>
        <p><label>Phone Number<br><input type="tel" name="your_phone"></label></p>
        <p><label>Property of Interest<br><input type="text" name="property_name" value="<?php echo esc_attr($property_name); ?>"></label></p>
        <?php if (!$availability): ?>
            <p><label>Preferred Check-in Date<br><input type="date" name="checkin_date"></label></p>
            <p><label>Preferred Check-out Date<br><input type="date" name="checkout_date"></label></p>
        <?php endif; ?>
        <p><label>Number of Guests<br><input type="number" name="guests" min="1" max="10"></label></p>
        <p><label>Message<br><textarea name="your_message" rows="5"></textarea></label></p>
        <p><button type="submit" class="btn btn--primary">Send Inquiry</button></p>

        <?php if ($availability): ?>
            </div>
        <?php endif; ?>
    </form>
    <?php
}

// A scanner or spam bot can submit this form as fast as it can send requests
// — the honeypot only catches submissions that fill every field, and a vuln
// scanner's per-parameter fuzzing leaves it empty most of the time (an OWASP
// ZAP active scan flooded the inbox with dozens of real emails on 2026-08-24,
// each one a different attack payload that happened to pass name/email
// validation). Cap real sends per IP within a short window.
//
// $attempt_number is this request's count AFTER an atomic DB increment
// (see cozumel_inquiry_register_attempt), not a pre-increment read — a
// plain get-then-set transient lets concurrent requests all read the same
// stale count and all get admitted, which defeats the cap under exactly
// the kind of parallel fuzzing this exists to stop.
function cozumel_inquiry_rate_limit_exceeded(int $attempt_number, int $limit = 5): bool {
    return $attempt_number > $limit;
}

// Atomically increments and returns this IP's attempt count for the
// current window, resetting the window if it has expired. Not unit
// tested — it's a thin DB-touching wrapper around the pure limit check
// above, same pattern as the add_action/add_filter wiring elsewhere in
// this file.
function cozumel_inquiry_register_attempt(string $ip, int $window_seconds = 600): int {
    global $wpdb;
    $key = 'cozumel_inquiry_rl_' . md5($ip);
    $value_option = '_transient_' . $key;
    $timeout_option = '_transient_timeout_' . $key;
    $now = time();

    $expires = (int) get_option($timeout_option);
    if ($expires < $now) {
        // Starting (or restarting an expired) window. A race here just
        // means two requests can both reset to 0 in the same instant —
        // harmless, self-corrects on the next request, unlike a race on
        // the increment/check itself which is what actually mattered.
        update_option($timeout_option, $now + $window_seconds, false);
        update_option($value_option, 0, false);
    }

    // Single atomic UPDATE — concurrent requests each get a distinct
    // post-increment value, so they can't all pass the limit check below.
    $wpdb->query($wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = option_value + 1 WHERE option_name = %s",
        $value_option
    ));

    return (int) get_option($value_option);
}

function cozumel_handle_inquiry_submission() {
    $redirect_to = !empty($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : home_url('/');

    if (!isset($_POST['cozumel_inquiry_nonce']) || !wp_verify_nonce($_POST['cozumel_inquiry_nonce'], 'cozumel_inquiry')) {
        wp_safe_redirect(add_query_arg('inquiry', 'error', $redirect_to));
        exit;
    }

    // Honeypot: bots fill every field, humans never see this one. Silently
    // pretend success so bots don't learn to leave it blank.
    if (!empty($_POST['website'])) {
        wp_safe_redirect(add_query_arg('inquiry', 'sent', $redirect_to));
        exit;
    }

    $attempt_number = cozumel_inquiry_register_attempt($_SERVER['REMOTE_ADDR'] ?? '');
    if (cozumel_inquiry_rate_limit_exceeded($attempt_number)) {
        wp_safe_redirect(add_query_arg('inquiry', 'error', $redirect_to));
        exit;
    }

    $name = sanitize_text_field($_POST['your_name'] ?? '');
    $email = sanitize_email($_POST['your_email'] ?? '');

    if (empty($name) || !is_email($email)) {
        wp_safe_redirect(add_query_arg('inquiry', 'error', $redirect_to));
        exit;
    }

    $phone = sanitize_text_field($_POST['your_phone'] ?? '');
    $property_name = sanitize_text_field($_POST['property_name'] ?? '');
    $checkin = sanitize_text_field($_POST['checkin_date'] ?? '');
    $checkout = sanitize_text_field($_POST['checkout_date'] ?? '');
    $guests = sanitize_text_field($_POST['guests'] ?? '');
    $message = sanitize_textarea_field($_POST['your_message'] ?? '');

    $subject = sprintf('New Inquiry from %s — %s', $name, $property_name ?: 'General');
    $body = "Name: $name\nEmail: $email\nPhone: $phone\nProperty: $property_name\n" .
            "Check-in: $checkin\nCheck-out: $checkout\nGuests: $guests\n\nMessage:\n$message\n";

    $sent = wp_mail('home@cozumelhomes.net', $subject, $body, ['Reply-To: ' . $name . ' <' . $email . '>']);

    wp_safe_redirect(add_query_arg('inquiry', $sent ? 'sent' : 'error', $redirect_to));
    exit;
}
if (function_exists('add_action')) {
    add_action('admin_post_cozumel_inquiry', 'cozumel_handle_inquiry_submission');
    add_action('admin_post_nopriv_cozumel_inquiry', 'cozumel_handle_inquiry_submission');
}
