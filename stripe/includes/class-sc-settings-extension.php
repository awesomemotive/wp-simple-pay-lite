<?php

// TODO: Add direct file access check

class SC_Settings_Extension extends MM_Settings_Callbacks {
	
	protected static $settings;
	
	/*
	 * Class constructor
	 * 
	 * We must define this to avoid errors with the parent class __constructor() being loaded
	 * We can use this to set our settings in the child class
	 */
	public function __construct() {	
		self::$settings = parent::$settings;	
	}
	
	/*
	 * Adds the child class to the "view" of the main class
	 * Each class the extends settings must do this so that we can make sure the callbacks can be found
	 */
	public function add_child_class() {
		parent::add_child( get_called_class() );
	}
	
	
	/**
	 * Below we can define any extended functionality callbacks
	 */
	
	function toggle_control_callback( $args ) {

		$checked = ( isset( self::$settings->saved_settings[ $args['id'] ] ) ? checked( 1, self::$settings->saved_settings[ $args['id'] ], false ) : '' );

		$html = '<div class="sc-toggle-switch-wrap">
				<label class="switch-light switch-candy switch-candy-blue" onclick="">
					<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>
					<span>
					  <span>Test</span>
					  <span>Live</span>
					</span>
					<a></a>
				</label></div>';

		echo $html;
	}
}


// We need to do this to make it all work correctly
$ext = new SC_Settings_Extension();
$ext->add_child_class();