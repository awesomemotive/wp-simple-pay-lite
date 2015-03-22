<?php

/**
 * Simple Stripe Checkout
 *
 * @package   SC
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @link      http://wpstripe.net
 * @copyright 2014 Phil Derksen
 *
 * @wordpress-plugin
 * Plugin Name: Simple Stripe Checkout
 * Plugin URI: http://wpstripe.net
 * Description: Add a Stripe Checkout form overlay to your site in minutes using shortcodes.
 * Version: 1.3.0
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

if ( ! defined( 'SC_PATH' ) ) {
	define( 'SC_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SC_CLASS_PATH' ) ) {
	define( 'SC_CLASS_PATH', plugin_dir_path( __FILE__ ) . 'classes/' );
}

if ( ! defined( 'SC_CSS_PATH' ) ) {
	define( 'SC_CSS_PATH', plugins_url( '', __FILE__ ) . '/assets/css/' );
}

if ( ! defined( 'SC_JS_PATH' ) ) {
	define( 'SC_JS_PATH', plugins_url( '', __FILE__ ) . '/assets/js/' );
}

if ( ! defined( 'SC_IMG_PATH' ) ) {
	define( 'SC_IMG_PATH', plugins_url( '', __FILE__ ) . '/assets/img/' );
}

if ( ! defined( 'SC_INCLUDES_PATH' ) ) {
	define( 'SC_INCLUDES_PATH', plugin_dir_path( __FILE__ ) . 'includes/' );
}

if ( ! defined( 'SC_LANGUAGES_PATH' ) ) {
	define( 'SC_LANGUAGES_PATH', plugin_dir_path( __FILE__ ) . 'languages/' );
}

if ( ! defined( 'SC_LIBRARIES_PATH' ) ) {
	define( 'SC_LIBRARIES_PATH', plugin_dir_path( __FILE__ ) . 'libraries/' );
}

if ( ! defined( 'SC_VIEWS_PATH' ) ) {
	define( 'SC_VIEWS_PATH', plugin_dir_path( __FILE__ ) . 'views/' );
}

require_once( SC_CLASS_PATH . 'class-stripe-checkout.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Stripe_Checkout', 'activate' ) );

Stripe_Checkout::get_instance();

