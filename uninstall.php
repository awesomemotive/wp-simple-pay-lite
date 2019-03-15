<?php

// Exit if not uninstalling from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$general = get_option( 'simpay_settings_general' );

// Check save settings option before removing everything
if ( ! isset( $general['general_misc']['save_settings'] ) ) {


	// First remove the payment confirmation pages
	$success_page = $general['general']['success_page'];
	$failure_page = $general['general']['failure_page'];

	wp_delete_post( $success_page, true );
	wp_delete_post( $failure_page, true );

	// Remove main options
	delete_option( 'simpay_settings' );

	// Remove misc options
	delete_option( 'simpay_use_php_sessions' );
	delete_option( 'simpay_dismiss_ssl' );
	delete_option( 'simpay_dismiss_dropping_php53_bitcoin' );

	// Remove settings options
	delete_option( 'simpay_settings_general' );
	delete_option( 'simpay_settings_keys' );
	delete_option( 'simpay_settings_display' );
	delete_option( 'simpay_settings_shipping_billing' );

	// Remove legacy options
	delete_option( 'simpay_preview_form_id' );

	// Delete form posts.
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'simple-pay' );" );

	// Delete forms postmeta.
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

}

// Check if we need to remove legacy settings
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
