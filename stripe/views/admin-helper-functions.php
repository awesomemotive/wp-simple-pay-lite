<?php

/**
 * Admin helper functions to get the base plugin tab and help tab set
 * 
 * @since 1.1.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
		'default' => __( 'Default Settings', 'sc' )
	);
	
	return apply_filters( 'sc_admin_tabs', $tabs );
	
}

/*
 * Return the Admin Help tabs
 * 
 * @since 1.1.1
 */
function sc_get_admin_help_tabs() {
	$tabs = array();
	
	$tabs = array( 
		'base' => __( 'Base Shortcodes', 'sc' )
	);
	
	return apply_filters( 'sc_help_tabs', $tabs );
}

/**
 * Use action to load base help file
 * 
 * @since 1.1.1
 */
function sc_load_help() {
	include_once( 'admin-help-stripe-checkout.php' );
}
add_action( 'sc_help_display_base', 'sc_load_help' );


/**
 * Add Licenses admin tab
 * 
 * @since 1.1.1
 */
function sc_license_tab( $tabs ) {
	$tabs['licenses'] = __( 'Licenses', 'sc' );

	return $tabs;
}
add_filter( 'sc_admin_tabs', 'sc_license_tab' );
	