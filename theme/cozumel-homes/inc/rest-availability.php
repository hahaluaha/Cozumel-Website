<?php
add_action('rest_api_init', function () {
    register_rest_route('cozumel/v1', '/availability/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'cozumel_get_availability',
        'permission_callback' => '__return_true',
        'args'                => [
            'id' => ['validate_callback' => function ($param) {
                return is_numeric($param);
            }],
        ],
    ]);
});

function cozumel_get_availability(WP_REST_Request $request): WP_REST_Response {
    $property_id = (int) $request['id'];

    if (get_post_type($property_id) !== 'rental-property') {
        return new WP_REST_Response(['error' => 'not a rental property'], 404);
    }

    if (get_post_status($property_id) !== 'publish') {
        return new WP_REST_Response(['error' => 'not a rental property'], 404);
    }

    $airbnb_raw = get_post_meta($property_id, 'airbnb_blocked_dates', true);
    $airbnb_ranges = json_decode($airbnb_raw ?: '[]', true);
    if (!is_array($airbnb_ranges)) {
        $airbnb_ranges = [];
    }

    $manual_raw = get_post_meta($property_id, 'manual_blocked_dates', true);
    $manual_ranges = json_decode($manual_raw ?: '[]', true);
    if (!is_array($manual_ranges)) {
        $manual_ranges = [];
    }

    $combined = array_merge($airbnb_ranges, $manual_ranges);
    $buffered = cozumel_ical_apply_buffer($combined, 1);

    $response = new WP_REST_Response($buffered, 200);
    $response->header('Cache-Control', 'public, max-age=1800');
    return $response;
}
