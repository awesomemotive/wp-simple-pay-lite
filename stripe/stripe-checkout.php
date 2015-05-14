<?php

/**
 * Simple Stripe Checkout
 *
 * @package   SC
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @link      http://wpstripe.net
 * @copyright 2014-2015 Phil Derksen
 *
 * @wordpress-plugin
 * Plugin Name: Simple Stripe Checkout
 * Plugin URI: http://wpstripe.net
 * Description: Add a Stripe Checkout form overlay to your site in minutes using shortcodes.
 * Version: 1.3.3
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

if ( ! defined( 'SC_MAIN_FILE' ) ) {
	define( 'SC_MAIN_FILE', __FILE__ );
}

if( ! defined( 'SC_PATH' ) ) {
	define( 'SC_PATH', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'SC_URL' ) ) {
	define( 'SC_URL', plugins_url( '', __FILE__ ) . '/' );
}

require_once( SC_PATH . 'class-stripe-checkout.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( SC_MAIN_FILE, array( 'Stripe_Checkout', 'activate' ) );

// Check for required and recommended versions of PHP before loading plugin.
// https://github.com/WPupdatePHP/wp-update-php
require_once( SC_PATH . 'libraries/WPUpdatePhp.php' );

$updatePhp = new WPUpdatePhp( '5.3', '5.4' );
$updatePhp->set_plugin_name( 'Simple Stripe Checkout' );

// Show admin notice and don't execute rest of plugin if it doesn't meet required version of PHP.
// Note the plugin will still be active.
if ( $updatePhp->does_it_meet_required_php_version( PHP_VERSION ) ) {

	// TODO Uncomment recommended admin notice once it can be hidden by user.
	// Show admin notice for recommended version of PHP, but if required version still met continue loading plugin.
	//$updatePhp->does_it_meet_recommended_php_version( PHP_VERSION );

	Stripe_Checkout::get_instance();
}
