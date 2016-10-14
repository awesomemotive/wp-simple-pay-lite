<?php

/**
 * Fired when the plugin is uninstalled.
 */

// If uninstall, not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'sc_settings' );

if ( ! isset( $settings['uninstall_save_settings'] ) || 1 != $settings['uninstall_save_settings'] ) {

	// Remove options used by current version of the plugin.
	delete_option( 'sc_settings' );
	delete_option( 'sc_set_defaults' );
	delete_option( 'sc_had_upgrade' );
	delete_option( 'sc_upgrade_has_run' );
	delete_option( 'sc_show_admin_install_notice' );
	delete_option( 'sc_show_api_notice' );

	// Remove options that may be hanging around from old versions.
	delete_option( 'sc_settings_master' );
	delete_option( 'sc_settings_default' );
	delete_option( 'sc_settings_keys' );
	delete_option( 'sc_has_run' );
	delete_option( 'sc_version' );
	delete_option( 'sc_settings_licenses' );
	delete_option( 'sc_licenses' );
	delete_option( 'sc_license' );
}
