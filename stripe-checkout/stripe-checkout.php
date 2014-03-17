<?php

/**
 * Simple Stripe Checkout
 *
 * @package   sc
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @link      http://philderksen.com
 * @copyright 2014 Phil Derksen
 *
 * @wordpress-plugin
 * Plugin Name: Simple Stripe Checkout
 * Plugin URI: http://philderksen.com/
 * Description: Add a simple Stripe Checkout button and overlay to your site using a shortcode.
 * Version: 1.0.0
 * Author: Phil Derksen
 * Author URI: http://philderksen.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: sc
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) )
	exit;

require_once( plugin_dir_path( __FILE__ ) . 'class-stripe-checkout.php' );

define( 'SC_MAIN_FILE', __FILE__ );

// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
register_activation_hook( __FILE__, array( 'Stripe_Checkout', 'activate' ) );

Stripe_Checkout::get_instance();
