<?php

class MM_Settings_Text extends MM_Settings_Callbacks {
	
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
	
	
	public function text_callback( $args ) {
		
		//echo '<pre>' . print_r( self::$settings, true ) . '</pre>';
		
		if ( isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
			$value = self::$settings->saved_settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '';
		$html = "\n" . '<input type="text" class="' . $size . '" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";

		// Render and style description text underneath if it exists.
		if ( ! empty( $args['desc'] ) )
			$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";

		echo $html;
	}
	
	public function checkbox_callback( $args ) {
			
		$checked = ( isset( self::$settings->saved_settings[$args['id']] ) ? checked( 1, self::$settings->saved_settings[$args['id']], false ) : '' );

		$html = "\n" . '<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

		// Render description text directly to the right in a label if it exists.
		if ( ! empty( $args['desc'] ) )
			$html .= '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";

		echo $html;
	}
}


// We need to do this to make it all work correctly
$text = new MM_Settings_Text();
$text->add_child_class();