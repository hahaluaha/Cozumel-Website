<?php
// The WP REST API exposes usernames to anyone by default (`/wp-json/wp/v2/users`),
// which hands an attacker the exact login name to target with a brute-force
// attempt. This site's WordPress users are real admin logins, not throwaway
// accounts, so that endpoint gets removed for anyone not already logged in.
function cozumel_filter_users_endpoints(array $endpoints, bool $is_logged_in): array {
    if (!$is_logged_in) {
        unset($endpoints['/wp/v2/users']);
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $endpoints;
}

if (function_exists('add_filter')) {
    add_filter('rest_endpoints', function (array $endpoints): array {
        return cozumel_filter_users_endpoints($endpoints, is_user_logged_in());
    });
}

// WordPress's default login error text ("Invalid username" vs. "The
// password you entered is incorrect") confirms to an attacker whether a
// guessed username exists before they've even started brute-forcing the
// password. One generic message regardless of the actual cause removes
// that free confirmation step.
function cozumel_generic_login_error(string $error): string {
    return 'Login failed. Please check your username and password and try again.';
}

if (function_exists('add_filter')) {
    add_filter('login_errors', 'cozumel_generic_login_error');
}

// Visiting /?author=1 (or the resulting /author/<nicename>/ archive) only
// resolves for a real user — and WordPress's own canonical redirect echoes
// the exact username back in the Location header, a classic enumeration
// vector WPScan flagged. Send guests home before WordPress's own
// redirect_canonical (also hooked on template_redirect, default priority
// 10) gets a chance to leak it — this hook runs at priority 0, earlier.
function cozumel_should_block_author_view(bool $is_author_view, bool $is_logged_in): bool {
    return $is_author_view && !$is_logged_in;
}

if (function_exists('add_action')) {
    add_action('template_redirect', function () {
        if (cozumel_should_block_author_view(is_author(), is_user_logged_in())) {
            wp_safe_redirect(home_url(), 301);
            exit;
        }
    }, 0);
}

// The emoji-detection script pulls wp-emoji-release.min.js from s.w.org —
// an external domain that would otherwise need its own CSP allowance for a
// feature modern browsers handle natively (unicode emoji render fine
// without it).
if (function_exists('remove_action')) {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
