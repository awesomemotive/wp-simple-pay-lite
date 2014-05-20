<?php

// Need to first check if there is currently a version stored to use for checking upgrades later
if ( ! get_option( 'sc_version' ) ) {
	echo 'hit #5<bR>';
	add_option( 'sc_version', $this->version );
} else {
	echo 'hit #6<bR>';
	add_option( 'sc_old_version', get_option( 'sc_version' ) );
}

// Only if the old version is less than the new version do we run our upgrade code.
if ( version_compare( get_option( 'sc_old_version' ), $this->version, '<' ) ) {
	echo 'hit #1<bR>';
	sc_do_all_upgrades();
	update_option( 'sc_upgrade_has_run', 1 );
} else {
	echo 'hit #2<bR>';
	// Delete our holder for the old version
	delete_option( 'sc_old_version' );
}

/**
 * Run through ALL upgrades
 *
 * @since   1.1.1
 *
 */
function sc_do_all_upgrades() {
	echo 'hit #3<bR>';
	$current_version = get_option( 'sc_old_version' );
	
	// if less than version 1.1.1 then upgrade
	if ( version_compare( $current_version, '1.1.1', '<' )) {
		   sc_v111_upgrade();
	}
	
	update_option( 'sc_upgrade_has_run', 1 );
	delete_option( 'sc_old_version' );
	
}


// Version 1.1.1 upgrades
function sc_v111_upgrade() {
	
	echo 'hit #4<bR>';
	$keys_options = get_option( 'sc_settings_general' );
	
	
		// Check if test mode was enabled
		if( isset( $keys_options['enable_test_key'] ) && $keys_options['enable_test_key'] == 1 ) {
			// if it was then we remove it because we are now checking if live is enabled, not test
			unset( $keys_options['enable_test_key'] );
		} else {

			// If was not in test mode then we need to set our new value to true
			$keys_options['enable_live_key'] = 1;
		}
		
		delete_option( 'sc_settings_general' );
		
		update_option( 'sc_settings_keys', $keys_options );
		
		add_option( 'sc_upgrade_has_run', 1 );
}
sc_do_all_upgrades();


//add_action( 'init', 'd' );