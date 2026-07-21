<?php
// Dev-only: WordPress core disables Application Passwords over plain HTTP
// unless the host is literally "localhost" (wp_is_application_passwords_available()).
// This site runs at http://cozumel-homes.local, so REST API Basic Auth is
// otherwise silently treated as anonymous. Only activate the override for
// this exact local dev host, never in production.
add_filter('wp_is_application_passwords_available', function ($available) {
    if (($_SERVER['HTTP_HOST'] ?? '') === 'cozumel-homes.local') {
        return true;
    }
    return $available;
});
