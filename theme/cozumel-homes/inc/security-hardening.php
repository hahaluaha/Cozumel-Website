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
