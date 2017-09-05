<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Setup plugin constants.

// Plugin version
if ( ! defined( 'SIMPAY_VERSION' ) ) {
	define( 'SIMPAY_VERSION', '1.6.0' );
}

// Plugin name
if ( ! defined( 'SIMPAY_NAME' ) ) {
	define( 'SIMPAY_NAME', 'WP Simple Pay Lite for Stripe' );
}

// Plugin folder path
if ( ! defined( 'SC_DIR_PATH' ) ) {
	define( 'SC_DIR_PATH', plugin_dir_path( __FILE__ ) );
}

// Plugin folder URL
if ( ! defined( 'SC_DIR_URL' ) ) {
	define( 'SC_DIR_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin root file
if ( ! defined( 'SC_PLUGIN_FILE' ) ) {
	define( 'SC_PLUGIN_FILE', __FILE__ );
}

// Main site base URL
if ( ! defined( 'SC_WEBSITE_BASE_URL' ) ) {
	define( 'SC_WEBSITE_BASE_URL', 'https://wpsimplepay.com/' );
}

// Pro upgrade URL
if ( ! defined( 'SIMPAY_PRO_UPGRADE_URL' ) ) {
	define( 'SIMPAY_PRO_UPGRADE_URL', SC_WEBSITE_BASE_URL . 'lite-vs-pro/' );
}

// Docs site base URL
if ( ! defined( 'SIMPAY_DOCS_BASE_URL' ) ) {
	define( 'SIMPAY_DOCS_BASE_URL', 'https://docs.wpsimplepay.com/' );
}

// Admin notice and stop execution if Pro plugin found.
// Do this before anything else that uses the plugins_loaded hook runs to avoid conflicts.
add_action( 'plugins_loaded', 'simpay_pro_plugin_check', 1 );

function simpay_pro_plugin_check() {
	if ( class_exists( 'Stripe_Checkout_Pro' ) || class_exists( 'Simple_Pay_Pro' ) ) {
		add_action( 'admin_notices', 'simpay_pro_active_notice' );

		return;
	}
}

function simpay_pro_active_notice() {
	echo '<div class="error"><p>' . __( 'WP Simple Pay Lite and Pro cannot be active simultaneously. Please deactivate one of them to proceed.', 'stripe' ) . '</p></div>';
}

// Plugin requirements class.
require_once 'classes/wp-requirements.php';

// Check plugin requirements before loading plugin.
$this_plugin_checks = new SimPay_WP_Requirements( SIMPAY_NAME, plugin_basename( __FILE__ ), array(
		'PHP'        => '5.3.3',
		'WordPress'  => '4.3',
		'Extensions' => array(
			'curl',
			'json',
			'mbstring',
		),
	) );
if ( $this_plugin_checks->pass() === false ) {
	$this_plugin_checks->halt();

	return;
}

// Load the plugin main class.
require_once SC_DIR_PATH . 'classes/class-stripe-checkout-shared.php';

// Register hook that is fired when the plugin is activated.
register_activation_hook( SC_PLUGIN_FILE, array( 'Stripe_Checkout', 'activate' ) );

// Create a global instance of our main class for this plugin so we can use it throughout all the other classes.
global $base_class;

// Let's get going finally!
$base_class = Stripe_Checkout::get_instance();
