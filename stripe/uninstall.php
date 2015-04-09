<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'sc_settings' );

if( $settings['sc_settings_uninstall_save_settings'] != 1 ) {

	delete_option( 'sc_settings' );

	// TODO Delete all pre-restructure settings.
}
