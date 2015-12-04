<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'sc_settings' );

if ( $settings['uninstall_save_settings'] != 1 ) {

	delete_option( 'sc_settings' );
	delete_option( 'sc_had_upgrade' );
	delete_option( 'sc_set_defaults' );

	// Also remove old plugin options
	delete_option( 'sc_settings_master' );
	delete_option( 'sc_settings_default' );
	delete_option( 'sc_settings_keys' );
	delete_option( 'sc_show_admin_install_notice' );
	delete_option( 'sc_has_run' );
	delete_option( 'sc_version' );
	delete_option( 'sc_upgrade_has_run' );
	delete_option( 'sc_settings_licenses' );
	delete_option( 'sc_licenses' );
}
