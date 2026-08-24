<?php
require_once __DIR__ . '/test-helpers.php';
require_once __DIR__ . '/../theme/cozumel-homes/inc/security-hardening.php';

$endpoints = [
    '/wp/v2/users' => ['methods' => ['GET']],
    '/wp/v2/users/(?P<id>[\d]+)' => ['methods' => ['GET']],
    '/wp/v2/posts' => ['methods' => ['GET']],
];

$guest_result = cozumel_filter_users_endpoints($endpoints, false);
assert_equal(isset($guest_result['/wp/v2/users']), false, 'removes the users list endpoint for guests');
assert_equal(isset($guest_result['/wp/v2/users/(?P<id>[\d]+)']), false, 'removes the single-user endpoint for guests');
assert_equal(isset($guest_result['/wp/v2/posts']), true, 'leaves unrelated endpoints untouched for guests');

$logged_in_result = cozumel_filter_users_endpoints($endpoints, true);
assert_equal(isset($logged_in_result['/wp/v2/users']), true, 'keeps the users endpoint for logged-in requests');

test_summary_and_exit();
