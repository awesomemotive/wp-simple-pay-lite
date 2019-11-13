<?php
/**
 * Plugin Name: WP Simple Pay Lite
 * Plugin URI: https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes.
 * Author: Sandhills Development, LLC
 * Author URI: https://sandhillsdev.com
 * Version: 2.3.2
 * Text Domain: stripe
 * Domain Path: /languages
 */

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright 2014-2019 Sandhills Development, LLC. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Don't run if Pro has been installed.
if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {

	//
	// Shared
	//
	if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
		define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
	}

	//
	// Lite/Pro-specific.
	//
	define( 'SIMPLE_PAY_VERSION', '2.3.2' );

	if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
		define( 'SIMPLE_PAY_PLUGIN_NAME', 'WP Simple Pay Lite' );
	}

	if ( ! defined( 'SIMPLE_PAY_ITEM_NAME' ) ) {
		define( 'SIMPLE_PAY_ITEM_NAME', 'WP Simple Pay Lite' );
	}

	//
	// Stripe.
	//
	if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {
		define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2019-08-14' );
	}

	if ( ! defined( 'SIMPLE_PAY_STRIPE_PARTNER_ID' ) ) {
		define( 'SIMPLE_PAY_STRIPE_PARTNER_ID', 'pp_partner_DKkf27LbiCjOYt' );
	}

	//
	// Helpers.
	//
	if ( ! defined( 'SIMPLE_PAY_MAIN_FILE' ) ) {
		define( 'SIMPLE_PAY_MAIN_FILE', __FILE__ );
	}

	if ( ! defined( 'SIMPLE_PAY_URL' ) ) {
		define( 'SIMPLE_PAY_URL', plugin_dir_url( __FILE__ ) );
	}

	if ( ! defined( 'SIMPLE_PAY_ASSETS' ) ) {
		define( 'SIMPLE_PAY_ASSETS', plugin_dir_url( __FILE__ ) . 'assets/' );
	}

	if ( ! defined( 'SIMPLE_PAY_DIR' ) ) {
		define( 'SIMPLE_PAY_DIR', plugin_dir_path( __FILE__ ) );
	}

	if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
		define( 'SIMPLE_PAY_INC', plugin_dir_path( __FILE__ ) . 'includes/' );
	}

	// Compatibility files.
	require_once( SIMPLE_PAY_DIR . 'includes/core/boostrap/compatibility.php' );

	if ( SimplePay\Core\Bootstrap\Compatibility\server_requirements_met() ) {
		// Autoloader.
		require_once( SIMPLE_PAY_DIR . 'vendor/autoload.php' );
		require_once( SIMPLE_PAY_DIR . 'includes/autoload.php' );

		// Plugin files.
		require_once( SIMPLE_PAY_DIR . 'includes/core/main.php' );
	} else {
		SimplePay\Core\Bootstrap\Compatibility\show_admin_notices();
	}

} else {
	deactivate_plugins( plugin_basename( SIMPLE_PAY_MAIN_FILE ) );
}
