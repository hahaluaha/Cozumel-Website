<?php
function cozumel_register_post_types() {
    register_post_type('rental-property', [
        'labels' => [
            'name'          => 'Rental Properties',
            'singular_name' => 'Rental Property',
            'add_new_item'  => 'Add New Rental Property',
            'edit_item'     => 'Edit Rental Property',
            'view_item'     => 'View Rental Property',
            'all_items'     => 'All Rental Properties',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'rentals'],
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-building',
        'menu_position' => 5,
    ]);

    register_post_type('forsale-property', [
        'labels' => [
            'name'          => 'For Sale Properties',
            'singular_name' => 'For Sale Property',
            'add_new_item'  => 'Add New For Sale Property',
            'edit_item'     => 'Edit For Sale Property',
            'view_item'     => 'View Property',
            'all_items'     => 'All For Sale Properties',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => ['slug' => 'for-sale'],
        'supports'     => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-store',
        'menu_position' => 6,
    ]);
}
add_action('init', 'cozumel_register_post_types');

// Flush rewrite rules on theme activation (runs once)
function cozumel_flush_rewrite_rules() {
    cozumel_register_post_types();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'cozumel_flush_rewrite_rules');
