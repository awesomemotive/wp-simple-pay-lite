<?php

/**
 * WP Simple Pay Lite for Stripe
 *
 * @package   SC
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @link      http://wpsimplepay.com
 * @copyright 2014-2015 Phil Derksen
 *
 * @wordpress-plugin
 * Plugin Name: WP Simple Pay Lite for Stripe
 * Plugin URI: http://wpsimplepay.com
 * Description: Add highly optimized Stripe checkout form overlays to your site in a few simple steps.
 * Version: 1.4.0.1
 * Author: Phil Derksen
 * Author URI: http://philderksen.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/pderksen/WP-Stripe-Checkout
 * Text Domain: sc
 * Domain Path: /languages/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setup constant for main file path
if ( ! defined( 'SC_MAIN_FILE' ) ) {
	define( 'SC_MAIN_FILE', __FILE__ );
}

// Set up constant for directory path
if ( ! defined( 'SC_DIR_PATH' ) ) {
	define( 'SC_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// Set up constant for Directory URL
if ( ! defined( 'SC_DIR_URL' ) ) {
	define( 'SC_DIR_URL', plugin_dir_url( __FILE__ ) );
}

// Website for this plugin
if ( ! defined( 'SC_WEBSITE_BASE_URL' ) ) {
	define( 'SC_WEBSITE_BASE_URL', 'http://wpstripe.net/' );
}

// Include main class file
require_once( SC_DIR_PATH . 'classes/class-stripe-checkout.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( SC_MAIN_FILE, array( 'Stripe_Checkout', 'activate' ) );

// Check for minimum version of PHP before loading plugin.
// Show admin notice if it doesn't.
// https://github.com/WPupdatePHP/wp-update-php
// plugin-name branch for now (to use set_plugin_name method)
require_once( SC_DIR_PATH . 'libraries/WPUpdatePhp.php' );

$updatePhp = new WPUpdatePhp( '5.3', '5.4' );
$updatePhp->set_plugin_name( 'WP Simple Pay Lite for Stripe' );

if ( $updatePhp->does_it_meet_required_php_version( PHP_VERSION ) ) {
	
	// TODO Uncomment recommended admin notice once it can be hidden by user.
	// Show admin notice for recommended version of PHP, but if required version still met continue loading plugin.
	//$updatePhp->does_it_meet_recommended_php_version( PHP_VERSION );
	
	// Set up global holding the base class instance so we can easily use it throughout
	global $base_class;
	$base_class = Stripe_Checkout::get_instance();
}
