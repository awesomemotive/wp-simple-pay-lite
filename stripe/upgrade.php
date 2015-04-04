<?php

/**
 * Upgrade functions
 *
 * @package   SC
 * @author    Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 * @license   GPL-2.0+
 * @copyright 2014 Phil Derksen
 */

add_action( 'init', 'sc_upgrade' );

/**
 * Main SC Upgrade function. Call this and branch of from here depending on what we need to do
 * 
 * @since 2.0.0
 */
function sc_upgrade() {
	
	global $sc_options;
	
	$version = $sc_options->get_setting_value( 'old_version' );
	
	if( null !== $version ) {
		
		// Check if under version 2 and run the v2 upgrade if we are
		if( version_compare( $version, '1.1.1', '<' ) && null === $sc_options->get_setting_value( 'sc_upgrade_has_run' ) ) {
			sc_v111_upgrade();
		}
		
		if( version_compare( $version, '1.3.1', '<' ) && null === $sc_options->get_setting_value( 'sc_upgrade_has_run' ) ) {
			sc_v113_upgrade();
		}
	}
	
	$new_version = Stripe_Checkout::get_instance()->version;
	$sc_options->add_setting( 'sc_version', $new_version );
	
	$sc_options->add_setting( 'upgrade_has_run', 1 );
}

function sc_v113_upgrade() {
	
	global $sc_options;
	
	// TODO: Remove old options?
	
	// sc_settings_master holds a merge of all settings arrays tied to the Stripe plugin. This includes any settings that are implemented by users.
	$master = get_option( 'sc_settings_master' );
	
	// Loop through the old settings and add them to the new structure
	foreach( $master as $option => $value ) {
		$sc_options->add_setting( $option, $value );
	}
}

function sc_v111_upgrade() {
	$keys_options = get_option( 'sc_settings_general' );

	// Check if test mode was enabled
	if( isset( $keys_options['enable_test_key'] ) && $keys_options['enable_test_key'] == 1 ) {
		// if it was then we remove it because we are now checking if live is enabled, not test
		unset( $keys_options['enable_test_key'] );
	} else {

		// If was not in test mode then we need to set our new value to true
		$keys_options['enable_live_key'] = 1;
	}

	// Delete old option settings from old version of SC
	delete_option( 'sc_settings_general' );

	// Update our new settings options
	update_option( 'sc_settings_keys', $keys_options );

	// Update version number option for future upgrades
	update_option( 'sc_version', $this->version );

	// Let us know that we ran the upgrade
	add_option( 'sc_upgrade_has_run', 1 );
}

