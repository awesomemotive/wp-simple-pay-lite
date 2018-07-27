<?php
/**
 * Plugin Name: WP Simple Pay (Lite Version)
 * Plugin URI:  https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes.
 * Author: WP Simple Pay
 * Author URI:  https://wpsimplepay.com
 * Version: 2.0.11
 * Text Domain: stripe
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
 * Copyright 2014-2018 Moonstone Media Group. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {

	define( 'SIMPLE_PAY_VERSION', '2.0.11' );

	if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
		define( 'SIMPLE_PAY_PLUGIN_NAME', 'WP Simple Pay' );
	}

	// Stripe API version should be in 'YYYY-MM-DD' format.
	if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {
		define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2018-05-21' );
	}

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

	if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
		define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
	}

	if ( ! defined( 'SIMPLE_PAY_MIN_PHP_VER' ) ) {
		define( 'SIMPLE_PAY_MIN_PHP_VER', '5.4' );
	}

	/**
	 * Show an error message for PHP version < SIMPLE_PAY_MIN_PHP_VER and don't load the plugin.
	 */
	if ( ! function_exists( 'simpay_admin_php_notice' ) ) {
		function simpay_admin_php_notice() {
			?>

			<div class="error">
				<p>
					<?php printf( esc_html__( '%1$s requires %2$s or higher.', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME, 'PHP ' . SIMPLE_PAY_MIN_PHP_VER ); ?>
				</p>
			</div>

			<?php
		}
	}

	if ( version_compare( PHP_VERSION, SIMPLE_PAY_MIN_PHP_VER, '<' ) ) {
		add_action( 'admin_notices', 'simpay_admin_php_notice' );

		return;
	}

	// Autoloader
	require_once( SIMPLE_PAY_DIR . 'vendor/autoload.php' );
	require_once( SIMPLE_PAY_INC . 'autoload.php' );

	// Core plugin (new)
	require_once( SIMPLE_PAY_INC . 'core/main.php' );

	// Upgrade promos
	require_once( SIMPLE_PAY_INC . 'promos/promo-loader.php' );

	// Core plugin (legacy)
	require_once( SIMPLE_PAY_INC . 'old/stripe-checkout.php' );

} else {

	deactivate_plugins( plugin_basename(SIMPLE_PAY_MAIN_FILE ) );
}

