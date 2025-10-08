<?php
/**
 * Aurora Theme Functions
 *
 * @package Aurora
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function aurora_setup() {
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
        'primary' => __('Primary Menu', 'aurora'),
        'footer' => __('Footer Menu', 'aurora'),
    ));
}
add_action('after_setup_theme', 'aurora_setup');

/**
 * Enqueue styles and scripts
 */
function aurora_enqueue_assets() {
    wp_enqueue_style('aurora-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
    
    // Enqueue Google Fonts
    wp_enqueue_style('aurora-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800;900&display=swap', array(), null);
    
    // Enqueue custom JavaScript
    wp_enqueue_script('aurora-script', get_template_directory_uri() . '/assets/script.js', array(), wp_get_theme()->get('Version'), true);
}
add_action('wp_enqueue_scripts', 'aurora_enqueue_assets');

/**
 * Register block patterns
 */
function aurora_register_patterns() {
    // Register the pattern category
    register_block_pattern_category('aurora', array(
        'label' => __('Aurora', 'aurora'),
    ));
}
add_action('init', 'aurora_register_patterns');

/**
 * Add skip to content link
 */
function aurora_skip_link() {
    echo '<a class="skip-link screen-reader-text" href="#main">' . __('Skip to content', 'aurora') . '</a>';
}
add_action('wp_body_open', 'aurora_skip_link');

/**
 * Set the front page as static
 */
function aurora_set_front_page() {
    // Only run on theme activation
    if (get_option('aurora_theme_activated') !== '1') {
        // Create pages if they don't exist
        $pages = array(
            'home' => array(
                'title' => 'Home',
                'content' => ''
            ),
            'about' => array(
                'title' => 'About',
                'content' => '<!-- wp:heading {"level":1} --><h1>About Us</h1><!-- /wp:heading --><!-- wp:paragraph --><p>We are passionate about creating beautiful, modern websites that help businesses grow and succeed in the digital world.</p><!-- /wp:paragraph -->'
            ),
            'services' => array(
                'title' => 'Services',
                'content' => '<!-- wp:heading {"level":1} --><h1>Our Services</h1><!-- /wp:heading --><!-- wp:paragraph --><p>We offer a comprehensive range of digital services to help your business thrive.</p><!-- /wp:paragraph -->'
            ),
            'portfolio' => array(
                'title' => 'Portfolio',
                'content' => '<!-- wp:heading {"level":1} --><h1>Our Portfolio</h1><!-- /wp:heading --><!-- wp:paragraph --><p>Take a look at some of our recent work and see what we can create for you.</p><!-- /wp:paragraph -->'
            ),
            'contact' => array(
                'title' => 'Contact',
                'content' => '<!-- wp:heading {"level":1} --><h1>Contact Us</h1><!-- /wp:heading --><!-- wp:paragraph --><p>Get in touch with us to discuss your project and how we can help you achieve your goals.</p><!-- /wp:paragraph -->'
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
                'about' => 'About',
                'services' => 'Services',
                'portfolio' => 'Portfolio',
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
        update_option('aurora_theme_activated', '1');
    }
}
add_action('after_switch_theme', 'aurora_set_front_page');

/**
 * Clean up on theme deactivation
 */
function aurora_deactivation() {
    delete_option('aurora_theme_activated');
}
add_action('switch_theme', 'aurora_deactivation');

/**
 * Add custom body classes
 */
function aurora_body_classes($classes) {
    $classes[] = 'aurora-theme';
    return $classes;
}
add_filter('body_class', 'aurora_body_classes');

/**
 * Add custom CSS for animations
 */
function aurora_custom_css() {
    ?>
    <style>
        /* Intersection Observer animations */
        .fade-in-up {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease-out;
        }
        
        .fade-in-up.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .fade-in-left {
            opacity: 0;
            transform: translateX(-30px);
            transition: all 0.6s ease-out;
        }
        
        .fade-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }
        
        .fade-in-right {
            opacity: 0;
            transform: translateX(30px);
            transition: all 0.6s ease-out;
        }
        
        .fade-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
    <?php
}
add_action('wp_head', 'aurora_custom_css');
