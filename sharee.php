<?php
/**
 * Plugin Name: Sharē
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
