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

// Generic login error message — same output regardless of the real cause
assert_equal(
    cozumel_generic_login_error('Invalid username.'),
    cozumel_generic_login_error('The password you entered is incorrect.'),
    'returns the same generic message regardless of the underlying WordPress error'
);
assert_equal(
    strpos(cozumel_generic_login_error('anything'), 'Invalid username') === false,
    true,
    'never leaks whether the username specifically was the problem'
);

// Author-view enumeration blocking
assert_equal(cozumel_should_block_author_view(true, false), true, 'blocks a guest viewing an author archive');
assert_equal(cozumel_should_block_author_view(true, true), false, 'allows a logged-in user to view an author archive');
assert_equal(cozumel_should_block_author_view(false, false), false, 'does nothing on non-author pages');

test_summary_and_exit();
