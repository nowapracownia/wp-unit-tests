<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    die( 'WP_TESTS_DIR not set' );
}

require_once $_tests_dir . '/includes/bootstrap.php';

/**
 * If inside a theme folder, consider uncommenting the following filter 
 */

/*
tests_add_filter( 'muplugins_loaded', function () {
    require dirname( __DIR__ ) . '/functions.php';
});
*/
