<?php

// Need to first check if there is currently a version stored to use for checking upgrades later
if ( ! get_option( 'sc_version' ) ) {
	add_option( 'sc_version', $this->version );
} else {
	add_option( 'sc_old_version', get_option( 'sc_version' ) );
}

// Only if the old version is less than the new version do we run our upgrade code.
if ( version_compare( get_option( 'sc_old_version' ), $this->version, '<' ) ) {
	// need to update pib_upgrade_has_run so that we don;t load the defaults in too
	update_option( 'sc_upgrade_has_run', 1 );
	sc_do_all_upgrades();
} else {
	// Delete our holder for the old version of PIB.
	delete_option( 'sc_old_version' );
}

/**
 * Run through ALL upgrades
 *
 * @since   3.0.0
 *
 */
function sc_do_all_upgrades() {
	
	$current_version = get_option( 'sc_old_version' );
	
	// if less than version 2 then upgrade
	if ( version_compare( $current_version, '1.1.1', '<' ))
		   sc_v111_upgrade();
	
	delete_option( 'sc_old_version' );
	
}

function sc_v111_upgrade() {
	
	
	$keys_options = get_option( 'sc_settings_keys' );
	
	
	// Check if test mode was enabled
	if( isset( $keys_options['enable_test_key'] ) && $keys_options['enable_test_key'] == 1 ) {
		
		// if it was then we remove it because we are now checking if live is enabled, not test
		unset( $keys_options['enable_test_key'] );
	} else {
		
		// If was not in test mode then we need to set our new value to true
		$keys_options['enable_live_key'] = 1;
	}
	
	update_option( 'sc_settings_keys', $keys_options );
}



//add_action( 'init', 'd' );