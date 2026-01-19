<?php
/**
 * Please replace values in this config file with the ones appropriate in your configuration.
 * Then rename the file to wp-tests-config.php
 */

/**
 * Test DB connection
 */
define( 'DB_NAME', 'wp_tests' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );

define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

/**
 * Constants necessary for testing
 */
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'WordPress Test Site' );

/**
 * PHP path
 */
define( 'WP_PHP_BINARY', '[path_to_your_php_binaries_here]' );

$_core_dir = getenv( 'WP_CORE_DIR' );

if ( ! $_core_dir ) {
    die( 'WP_CORE_DIR not set' );
}

/**
 * ABSPATH – WP root dir path
 */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', $_core_dir );
}