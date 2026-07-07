<?php
function cozumel_render_inquiry_form($property_name = '') {
    if (isset($_GET['inquiry']) && $_GET['inquiry'] === 'sent') {
        echo '<p style="color:#2a6fa8;font-weight:600">Thanks — your message has been sent. We\'ll get back to you soon.</p>';
    } elseif (isset($_GET['inquiry']) && $_GET['inquiry'] === 'error') {
        echo '<p style="color:#b23b3b;font-weight:600">Something went wrong sending your message. Please try again or email us directly at home@cozumelhomes.net.</p>';
    }
    ?>
    <form class="inquiry-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="cozumel_inquiry">
        <?php wp_nonce_field('cozumel_inquiry', 'cozumel_inquiry_nonce'); ?>
        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink() ?: home_url('/')); ?>">
        <p style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Leave this field empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </p>
        <p><label>Your Name<br><input type="text" name="your_name" required></label></p>
        <p><label>Email Address<br><input type="email" name="your_email" required></label></p>
        <p><label>Phone Number<br><input type="tel" name="your_phone"></label></p>
        <p><label>Property of Interest<br><input type="text" name="property_name" value="<?php echo esc_attr($property_name); ?>"></label></p>
        <p><label>Preferred Check-in Date<br><input type="date" name="checkin_date"></label></p>
        <p><label>Preferred Check-out Date<br><input type="date" name="checkout_date"></label></p>
        <p><label>Number of Guests<br><input type="number" name="guests" min="1" max="10"></label></p>
        <p><label>Message<br><textarea name="your_message" rows="5"></textarea></label></p>
        <p><button type="submit" class="btn btn--primary">Send Inquiry</button></p>
    </form>
    <?php
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
add_action('admin_post_cozumel_inquiry', 'cozumel_handle_inquiry_submission');
add_action('admin_post_nopriv_cozumel_inquiry', 'cozumel_handle_inquiry_submission');
