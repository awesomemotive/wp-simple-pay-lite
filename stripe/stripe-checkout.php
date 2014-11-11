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
 * Version: 1.2.8
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

require_once( plugin_dir_path( __FILE__ ) . 'class-stripe-checkout.php' );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Stripe_Checkout', 'activate' ) );

Stripe_Checkout::get_instance();
