<?php
/**
 * Plugin Name: WP Simple Pay Lite
 * Plugin URI: https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes.
 * Author: WP Simple Pay
 * Author URI: https://wpsimplepay.com
 * Version: 4.5.0
 * Text Domain: stripe
 * Domain Path: /languages
 */

namespace SimplePay;

use SimplePay\Core\Plugin;
use SimplePay\Core\Bootstrap\Compatibility;

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
 * Copyright 2014-2022 Sandhills Development, LLC. All rights reserved.
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
	define( 'SIMPLE_PAY_VERSION', '4.5.0' );

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
		define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2020-08-27' );
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
		define( 'SIMPLE_PAY_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) );
	}

	if ( ! defined( 'SIMPLE_PAY_DIR' ) ) {
		define( 'SIMPLE_PAY_DIR', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) );
	}

	if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
		define( 'SIMPLE_PAY_INC', plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
	}

	if ( ! defined( 'SIMPLE_PAY_INC_URL' ) ) {
		define( 'SIMPLE_PAY_INC_URL', plugin_dir_url( SIMPLE_PAY_MAIN_FILE ) . 'includes/' );
	}

	// Compatibility files.
	require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/compatibility.php' );

	if ( Compatibility\server_requirements_met() ) {
		// Autoloader.
		require_once( SIMPLE_PAY_DIR . 'vendor/autoload.php' );
		require_once( SIMPLE_PAY_DIR . 'includes/core/bootstrap/autoload.php' );

		// Plugin files.
		require_once( SIMPLE_PAY_DIR . 'includes/core/class-simplepay.php' );

		// New plugin container.
		$plugin = new Plugin( __FILE__ );
		$plugin->load();
	} else {
		Compatibility\show_admin_notices();
	}

} else {
	deactivate_plugins( plugin_basename( SIMPLE_PAY_MAIN_FILE ) );
}
