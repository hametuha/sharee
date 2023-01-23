<?php
/**
 * Plugin Name: Sharēe
 * Plugin URI:  https://github.com/hametuha/sharee
 * Description: User reward manager for WordPress
 * Version:     nightly
 * Author:      Hametuha INC.
 * Author URI:  https://hametuha.co.jp
 * License:     GPLv3 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-3.0.html
 * Text Domain: sharee
 * Domain Path: /languages
 */

// This file actually do nothing.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Invalid request.' );
}

// Load autoloader.
require __DIR__ . '/vendor/autoload.php';
\Hametuha\Sharee::get_instance();

// Enable payment list.
add_filter( 'sharee_should_enable', function( $enabled, $service ) {
	switch ( $service ) {
		case 'billing':
			return true;
		default:
			return $enabled;
	}
}, 10, 2 );

// If hashboard exists, enable it.
add_action( 'plugins_loaded', function() {
	if ( class_exists( 'Hametuha\\Hashboard' ) ) {
		Hametuha\Hashboard::get_instance();
		define( 'HASHBOARD', Hametuha\Hashboard::version() );
	}
} );
