<?php
/**
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
 *
 * @package   SC
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @link      http://wpstripe.net
 * @copyright 2014-2015 Phil Derksen
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define constants.
$stripe_checkout_requires = array( 'wp' => '3.9.0', 'php' => '5.3.0' );
$stripe_checkout_constants = array(
	'SC_REQUIRES'  => serialize( $stripe_checkout_requires ),
	'SC_MAIN_FILE' => __FILE__,
	'SC_URL'       => plugins_url( '', __FILE__ ) . '/',
	'SC_PATH'      => plugin_dir_path( __FILE__ ),
);
foreach( $stripe_checkout_constants as $constant => $value ) {
	if ( ! defined( $constant ) ) {
		define( $constant, $value );
	}
}

// Check plugin requirements.
include_once 'stripe-checkout-requirements.php';
$stripe_checkout_requirements = new Stripe_Checkout_Requirements( $stripe_checkout_requires );
if ( $stripe_checkout_requirements->pass() === false ) {

	// Display an admin notice explaining why the plugin can't work.
	function stripe_checkout_plugin_requirements() {
		$required = unserialize( SC_REQUIRES );
		if ( isset( $required['wp'] ) && isset( $required['php'] ) ) {
			global $wp_version;
			echo '<div class="error"><p>' . sprintf( __( 'Stripe Checkout requires PHP %1$s and WordPress %2$s to function properly. PHP version found: %3$s. WordPress installed version: %4$s. Please upgrade to meet the minimum requirements.', 'gce' ), $required['php'], $required['wp'], PHP_VERSION, $wp_version ) . '</p></div>';
		}
	}
	add_action( 'admin_notices', 'stripe_checkout_plugin_requirements' );

	$stripe_checkout_fails = $stripe_checkout_requirements->failures();
	if ( isset( $stripe_checkout_fails['php'] ) ) {

		// Deactivate the plugin
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		};
		function stripe_checkout_plugin_deactivate_self() {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
		add_action( 'admin_init', 'stripe_checkout_plugin_deactivate_self' );

		// Halt the rest of the plugin execution if PHP check fails.
		return;
	}

}

// Load the plugin.
require_once SC_PATH . 'class-stripe-checkout.php';

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( SC_MAIN_FILE, array( 'Stripe_Checkout', 'activate' ) );
Stripe_Checkout::get_instance();
