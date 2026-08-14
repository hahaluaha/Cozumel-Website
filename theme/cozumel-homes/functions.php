<?php
function cozumel_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), ['parent-style']);
    wp_enqueue_style('cozumel-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', ['child-style'], '1.0.0');

    if (is_singular(['rental-property', 'forsale-property'])) {
        wp_enqueue_script(
            'cozumel-carousel',
            get_stylesheet_directory_uri() . '/assets/js/carousel.js',
            [],
            '1.0.0',
            true
        );
    }

    if (is_singular('rental-property')) {
        wp_enqueue_script(
            'cozumel-availability-calendar',
            get_stylesheet_directory_uri() . '/assets/js/availability-calendar.js',
            [],
            '1.0.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'cozumel_enqueue_styles');

// Map provider: 'google' | 'apple' | 'openstreetmap'
define('COZUMEL_MAP_PROVIDER', 'openstreetmap');
define('COZUMEL_GOOGLE_MAPS_KEY', defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '');

require_once get_stylesheet_directory() . '/inc/post-types.php';
require_once get_stylesheet_directory() . '/inc/meta-fields.php';
require_once get_stylesheet_directory() . '/inc/inquiry-form.php';
require_once get_stylesheet_directory() . '/inc/dev-application-passwords.php';
require_once get_stylesheet_directory() . '/inc/cli-sync-calendars.php';
require_once get_stylesheet_directory() . '/inc/ical-sync.php';
require_once get_stylesheet_directory() . '/inc/rest-availability.php';

// Route wp_mail() through Google Workspace SMTP — the VPS has no local MTA,
// so PHP's default mail() silently fails. Credentials come from constants
// defined in wp-config.php (git-ignored), never committed here.
function cozumel_phpmailer_smtp($phpmailer) {
    if (!defined('COZUMEL_SMTP_USER') || !defined('COZUMEL_SMTP_PASS')) {
        return;
    }
    $phpmailer->isSMTP();
    $phpmailer->Host = 'smtp.gmail.com';
    $phpmailer->Port = 587;
    $phpmailer->SMTPAuth = true;
    $phpmailer->SMTPSecure = 'tls';
    $phpmailer->Username = COZUMEL_SMTP_USER;
    $phpmailer->Password = COZUMEL_SMTP_PASS;
    $phpmailer->From = COZUMEL_SMTP_USER;
    $phpmailer->FromName = 'Cozumel Homes';
}
add_action('phpmailer_init', 'cozumel_phpmailer_smtp');

function cozumel_enqueue_gallery_admin_assets($hook) {
    if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
    $screen = get_current_screen();
    if (!$screen || !in_array($screen->post_type, ['rental-property', 'forsale-property'], true)) return;

    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script(
        'cozumel-gallery-picker',
        get_stylesheet_directory_uri() . '/assets/js/gallery-picker.js',
        ['jquery', 'jquery-ui-sortable'],
        '1.0.0',
        true
    );
}
add_action('admin_enqueue_scripts', 'cozumel_enqueue_gallery_admin_assets');
