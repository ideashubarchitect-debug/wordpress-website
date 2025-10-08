<?php
/**
 * SaaS Reminder Theme Functions
 *
 * @package SaaS_Reminder
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function saas_reminder_setup() {
    // Add theme support for various features
    add_theme_support('wp-block-styles');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_theme_support('responsive-embeds');
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script'
    ));

    // Add editor styles
    add_editor_style('style.css');

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'saas-reminder'),
        'footer' => __('Footer Menu', 'saas-reminder'),
    ));
}
add_action('after_setup_theme', 'saas_reminder_setup');

/**
 * Enqueue styles and scripts
 */
function saas_reminder_enqueue_assets() {
    wp_enqueue_style('saas-reminder-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
}
add_action('wp_enqueue_scripts', 'saas_reminder_enqueue_assets');

/**
 * Register block patterns
 */
function saas_reminder_register_patterns() {
    // Register the pattern category
    register_block_pattern_category('saas-reminder', array(
        'label' => __('SaaS Reminder', 'saas-reminder'),
    ));
}
add_action('init', 'saas_reminder_register_patterns');

/**
 * Add skip to content link
 */
function saas_reminder_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#main">' . __('Skip to content', 'saas-reminder') . '</a>';
}
add_action('wp_body_open', 'saas_reminder_skip_link');

/**
 * Set the front page as static
 */
function saas_reminder_set_front_page() {
    // Only run on theme activation
    if (get_option('saas_reminder_theme_activated') !== '1') {
        // Create pages if they don't exist
        $pages = array(
            'home' => array(
                'title' => 'Home',
                'content' => ''
            ),
            'features' => array(
                'title' => 'Features',
                'content' => '<!-- wp:heading {"level":1} -->\n<h1>Features</h1>\n<!-- /wp:heading -->\n\n<!-- wp:pattern {"slug":"saas-reminder/features"} /-->'
            ),
            'pricing' => array(
                'title' => 'Pricing',
                'content' => '<!-- wp:heading {"level":1} -->\n<h1>Pricing</h1>\n<!-- /wp:heading -->\n\n<!-- wp:pattern {"slug":"saas-reminder/pricing"} /-->'
            ),
            'docs' => array(
                'title' => 'Documentation',
                'content' => '<!-- wp:heading {"level":1} -->\n<h1>Documentation</h1>\n<!-- /wp:heading -->\n\n<!-- wp:heading {"level":2} -->\n<h2>Getting Started</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Welcome to our task reminder app! Here\'s how to get started:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {"level":2} -->\n<h2>API Reference</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Our REST API allows you to integrate with your existing workflow.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:heading {"level":2} -->\n<h2>FAQ</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Find answers to common questions about our service.</p>\n<!-- /wp:paragraph -->'
            ),
            'contact' => array(
                'title' => 'Contact',
                'content' => '<!-- wp:heading {"level":1} -->\n<h1>Contact Us</h1>\n<!-- /wp:heading -->\n\n<!-- wp:group {"style":{"spacing":{"padding":{"top":"var(--wp--preset--spacing--40)","bottom":"var(--wp--preset--spacing--40)"}}},"className":"is-style-form"} -->\n<div class="wp-block-group is-style-form">\n<!-- wp:heading {"level":2} -->\n<h2>Get in Touch</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>Have questions? We\'d love to hear from you. Send us a message and we\'ll respond as soon as possible.</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:html -->\n<form>\n  <p>\n    <label for="name">Name *</label><br>\n    <input type="text" id="name" name="name" required>\n  </p>\n  <p>\n    <label for="email">Email *</label><br>\n    <input type="email" id="email" name="email" required>\n  </p>\n  <p>\n    <label for="subject">Subject</label><br>\n    <input type="text" id="subject" name="subject">\n  </p>\n  <p>\n    <label for="message">Message *</label><br>\n    <textarea id="message" name="message" rows="5" required></textarea>\n  </p>\n  <p>\n    <button type="submit" class="wp-block-button__link">Send Message</button>\n  </p>\n</form>\n<!-- /wp:html -->\n</div>\n<!-- /wp:group -->'
            )
        );

        foreach ($pages as $slug => $page_data) {
            $existing_page = get_page_by_path($slug);
            if (!$existing_page) {
                wp_insert_post(array(
                    'post_title' => $page_data['title'],
                    'post_content' => $page_data['content'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $slug
                ));
            }
        }

        // Set the home page as the front page
        $home_page = get_page_by_path('home');
        if ($home_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $home_page->ID);
        }

        // Create a primary menu
        $menu_id = wp_create_nav_menu('Primary Menu');
        if ($menu_id) {
            $menu_items = array(
                'home' => 'Home',
                'features' => 'Features',
                'pricing' => 'Pricing',
                'docs' => 'Documentation',
                'contact' => 'Contact'
            );

            foreach ($menu_items as $slug => $title) {
                $page = get_page_by_path($slug);
                if ($page) {
                    wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-title' => $title,
                        'menu-item-object-id' => $page->ID,
                        'menu-item-object' => 'page',
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish'
                    ));
                }
            }

            // Set the menu location
            $locations = get_theme_mod('nav_menu_locations');
            $locations['primary'] = $menu_id;
            set_theme_mod('nav_menu_locations', $locations);
        }

        // Mark theme as activated
        update_option('saas_reminder_theme_activated', '1');
    }
}
add_action('after_switch_theme', 'saas_reminder_set_front_page');

/**
 * Clean up on theme deactivation
 */
function saas_reminder_deactivation() {
    delete_option('saas_reminder_theme_activated');
}
add_action('switch_theme', 'saas_reminder_deactivation');
