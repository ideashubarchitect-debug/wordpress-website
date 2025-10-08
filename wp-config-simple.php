<?php
/**
 * Simple WordPress configuration for local development
 * Uses SQLite instead of MySQL
 */

// Enable SQLite support
define('DB_NAME', 'wordpress');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_HOST', 'localhost');

// Use SQLite
define('DB_TYPE', 'sqlite');
define('DB_FILE', __DIR__ . '/wp-content/database.sqlite');

// WordPress configuration
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Security keys (you can generate new ones)
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

$table_prefix = 'wp_';

// WordPress language
define('WPLANG', '');

// WordPress directory
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once( ABSPATH . 'wp-settings.php' );
?>
