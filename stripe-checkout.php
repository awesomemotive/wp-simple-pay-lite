<?php
/**
 * Plugin Name: WP Simple Pay Lite
 * Plugin URI:  https://wpsimplepay.com
 * Description: Add high conversion Stripe payment forms to your WordPress site in minutes. **Lite Version**
 * Author: Moonstone Media
 * URI:  https://wpsimplepay.com
 * Version: 2.0.2
 * Text Domain: simple-pay
 * Domain Path: /i18n
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
 * Copyright 2014-2017 Moonstone Media Group. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SIMPLE_PAY_VERSION' ) ) {

	define( 'SIMPLE_PAY_VERSION', '2.0.2' );

	// Plugin constants.
	$this_plugin_path = trailingslashit( dirname( __FILE__ ) );
	$this_plugin_dir  = plugin_dir_url( __FILE__ );

	if ( ! defined( 'SIMPLE_PAY_PLUGIN_NAME' ) ) {
		define( 'SIMPLE_PAY_PLUGIN_NAME', 'WP Simple Pay' );
	}

	if ( ! defined( 'SIMPLE_PAY_STRIPE_API_VERSION' ) ) {

		// Set the API Version from Stripe 'YYYY-MM-DD' format
		define( 'SIMPLE_PAY_STRIPE_API_VERSION', '2017-08-15' );
	}

	if ( ! defined( 'SIMPLE_PAY_MAIN_FILE' ) ) {
		define( 'SIMPLE_PAY_MAIN_FILE', __FILE__ );
	}

	if ( ! defined( 'SIMPLE_PAY_URL' ) ) {
		define( 'SIMPLE_PAY_URL', $this_plugin_dir );
	}

	if ( ! defined( 'SIMPLE_PAY_ASSETS' ) ) {
		define( 'SIMPLE_PAY_ASSETS', $this_plugin_dir . 'assets/' );
	}

	if ( ! defined( 'SIMPLE_PAY_PATH' ) ) {
		define( 'SIMPLE_PAY_PATH', $this_plugin_path );
	}

	if ( ! defined( 'SIMPLE_PAY_INC' ) ) {
		define( 'SIMPLE_PAY_INC', $this_plugin_path . 'includes/' );
	}

	if ( ! defined( 'SIMPLE_PAY_STORE_URL' ) ) {
		define( 'SIMPLE_PAY_STORE_URL', 'https://wpsimplepay.com/' );
	}

	// PHP minimum requirement check.
	if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
		add_action( 'admin_notices', 'simpay_admin_php_notice' );

		return;
	}



	/**
	 * Show an error message for PHP < 5.3 and don't load the plugin
	 */
	function simpay_admin_php_notice() {
		?>

		<div class="error">
			<p>
				<?php printf( esc_html__( '%s requires PHP 5.3 or higher.', 'stripe' ), SIMPLE_PAY_PLUGIN_NAME ); ?>
			</p>
		</div>

		<?php
	}

	include_once( 'vendor/autoload.php' );
	include_once( 'includes/autoload.php' );

	// Load new plugin.
	include_once( 'includes/core/main.php' );

	// Load the promos
	include_once( 'includes/promos/promo-loader.php' );

	// Load old plugin
	include_once( 'includes/old/stripe-checkout.php' );
} else {
	deactivate_plugins( plugin_basename(SIMPLE_PAY_MAIN_FILE ) );
}

