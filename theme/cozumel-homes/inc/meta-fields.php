<?php
// ── Register meta for REST API access ──────────────────────────────────────
function cozumel_register_meta_fields() {
    $rental_fields = [
        'mac_id', 'neighborhood', 'address', 'base_rate', 'status',
        'max_guests', 'bedrooms', 'bathrooms',
        'latitude', 'longitude', 'airbnb_ical_url', 'airbnb_listing_url',
    ];
    foreach ($rental_fields as $field) {
        register_post_meta('rental-property', $field, [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
    }

    $forsale_fields = [
        'mac_id', 'asking_price', 'listing_url', 'notes',
        'bedrooms', 'bathrooms', 'latitude', 'longitude',
    ];
    foreach ($forsale_fields as $field) {
        register_post_meta('forsale-property', $field, [
            'show_in_rest' => true,
            'single'       => true,
            'type'         => 'string',
        ]);
    }
}
add_action('init', 'cozumel_register_meta_fields');

// ── Admin meta boxes ────────────────────────────────────────────────────────
function cozumel_add_meta_boxes() {
    add_meta_box(
        'rental_details', 'Property Details',
        'cozumel_rental_meta_box_html', 'rental-property', 'normal', 'high'
    );
    add_meta_box(
        'forsale_details', 'Property Details',
        'cozumel_forsale_meta_box_html', 'forsale-property', 'normal', 'high'
    );
}
add_action('add_meta_boxes', 'cozumel_add_meta_boxes');

function cozumel_meta_field($key, $label, $post_id, $type = 'text') {
    $value = esc_attr(get_post_meta($post_id, $key, true));
    echo "<p><label style='font-weight:600'>" . esc_html($label) . "</label><br>";
    echo "<input type='{$type}' name='{$key}' value='{$value}' style='width:100%;margin-top:4px'></p>";
}

function cozumel_rental_meta_box_html($post) {
    wp_nonce_field('cozumel_save_meta', 'cozumel_meta_nonce');
    cozumel_meta_field('mac_id',              'Mac App ID (managed by sync — do not edit)', $post->ID);
    cozumel_meta_field('neighborhood',        'Neighborhood', $post->ID);
    cozumel_meta_field('address',             'Address', $post->ID);
    cozumel_meta_field('base_rate',           'Nightly Rate (USD)', $post->ID);
    cozumel_meta_field('status',              'Status (active / inactive / maintenance)', $post->ID);
    cozumel_meta_field('max_guests',          'Max Guests', $post->ID);
    cozumel_meta_field('bedrooms',            'Bedrooms', $post->ID);
    cozumel_meta_field('bathrooms',           'Bathrooms', $post->ID);
    cozumel_meta_field('latitude',            'Latitude (set once — not overwritten by sync)', $post->ID);
    cozumel_meta_field('longitude',           'Longitude (set once — not overwritten by sync)', $post->ID);
    cozumel_meta_field('airbnb_ical_url',     'Airbnb iCal Export URL', $post->ID);
    cozumel_meta_field('airbnb_listing_url',  'Airbnb Listing URL', $post->ID);
}

function cozumel_forsale_meta_box_html($post) {
    wp_nonce_field('cozumel_save_meta', 'cozumel_meta_nonce');
    cozumel_meta_field('mac_id',       'Mac App ID (managed by sync — do not edit)', $post->ID);
    cozumel_meta_field('asking_price', 'Asking Price (USD)', $post->ID);
    cozumel_meta_field('listing_url',  'External Listing URL', $post->ID);
    cozumel_meta_field('bedrooms',     'Bedrooms', $post->ID);
    cozumel_meta_field('bathrooms',    'Bathrooms', $post->ID);
    cozumel_meta_field('latitude',     'Latitude', $post->ID);
    cozumel_meta_field('longitude',    'Longitude', $post->ID);
    $notes = esc_textarea(get_post_meta($post->ID, 'notes', true));
    echo "<p><label style='font-weight:600'>Notes (internal — not shown publicly)</label><br>";
    echo "<textarea name='notes' style='width:100%;height:80px;margin-top:4px'>{$notes}</textarea></p>";
}

// ── Save meta on post save ──────────────────────────────────────────────────
function cozumel_save_meta($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['cozumel_meta_nonce']) ||
        !wp_verify_nonce($_POST['cozumel_meta_nonce'], 'cozumel_save_meta')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) return;

    $all_fields = [
        'mac_id', 'neighborhood', 'address', 'base_rate', 'status',
        'max_guests', 'bedrooms', 'bathrooms',
        'latitude', 'longitude', 'airbnb_ical_url', 'airbnb_listing_url',
        'asking_price', 'listing_url', 'notes',
    ];
    foreach ($all_fields as $field) {
        if (array_key_exists($field, $_POST)) {
            $value = ($field === 'notes')
                ? sanitize_textarea_field($_POST[$field])
                : sanitize_text_field($_POST[$field]);
            update_post_meta($post_id, $field, $value);
        }
    }
}
add_action('save_post', 'cozumel_save_meta');
