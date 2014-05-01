<?php

/* 
 * Get Admin Tabs and label
 * 
 * @since 1.1.1
 * 
 * array( $key => $value )
 * $key is the value that is used when making the setting option
 * $value is the display title of the tab
 * 
 * @return array
 */

function sc_get_admin_tabs() {
	
	$tabs = array();
	
	$tabs = array( 
		'keys'    => __( 'Stripe Keys' , 'sc' ),
		'default' => __( 'Default Settings', 'sc' ),
		'help'    => __( 'Help', 'sc' )
	);
	
	return apply_filters( 'sc_admin_tabs', $tabs );
	
}

/*
 * Function to include the admin help page as tab content
 * 
 * @since 1.1.1
 */
function sc_add_help_page() {
	include( 'admin-help.php' );
}
add_action( 'sc_settings_help', 'sc_add_help_page' );

/*
 * Function to remove the save button from the help tab
 * 
 * @since 1.1.1
 */
function sc_submit_button_help( $submit_button ) {
	return '';
}
add_filter( 'sc_submit_button_help', 'sc_submit_button_help' );

