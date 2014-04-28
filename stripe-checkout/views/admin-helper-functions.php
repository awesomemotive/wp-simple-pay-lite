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

/*function add_more_tabs( $tabs ) {
	
	$tabs['test_1'] = __( 'Test Tab #1', 'sc' );
	$tabs['test_2'] = __( 'Test Tab #2', 'sc' );
	
	return $tabs;
}
add_filter( 'sc_admin_tabs', 'add_more_tabs' );*/
