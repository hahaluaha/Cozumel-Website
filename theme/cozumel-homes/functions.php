<?php
function cozumel_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_uri(), ['parent-style']);
    wp_enqueue_style('cozumel-theme', get_stylesheet_directory_uri() . '/assets/css/theme.css', ['child-style'], '1.0.0');
}
add_action('wp_enqueue_scripts', 'cozumel_enqueue_styles');

// Map provider: 'google' | 'apple' | 'openstreetmap'
define('COZUMEL_MAP_PROVIDER', 'google');
define('COZUMEL_GOOGLE_MAPS_KEY', defined('GOOGLE_MAPS_API_KEY') ? GOOGLE_MAPS_API_KEY : '');

require_once get_stylesheet_directory() . '/inc/post-types.php';
require_once get_stylesheet_directory() . '/inc/meta-fields.php';
require_once get_stylesheet_directory() . '/inc/inquiry-form.php';
require_once get_stylesheet_directory() . '/inc/dev-application-passwords.php';
