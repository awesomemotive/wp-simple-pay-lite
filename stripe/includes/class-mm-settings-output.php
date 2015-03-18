<?php

class MM_Settings_Output extends MM_Settings {
	
	
	public function __construct( $option ) {
		parent::__construct( $option );
	}
	
	public function ajax_save_button( $label ) {
			echo '<button class="ajax_save button-primary">' . $label . '</button>';
		}
	
	public function textbox( $id, $classes = '' ) {
			
		$html = '<input type="text" class="' . esc_html( $classes ) . '" name="' . $this->get_setting_id( $id ) . '" id="' . $this->get_setting_id( $id ) . '" value="' . $this->get_setting_value( $id ) . '" />';

		echo $html;
	}
		
	/**
	 * 
	 * Method to output checkbox inputs
	 * 
	 * @param 
	 * @since 1.0.0
	 */
	public function checkbox( $id, $classes = '' ) {
		
		$value = $this->get_setting_value( $id );
		
		$checked = ( ! empty( $value ) ? checked( 1, $value, false ) : '' );
		
		$html = "\n" . '<input type="checkbox" class="' . esc_html( $classes ) . '" id="' . $this->get_setting_id( $id ) . '" name="' . $this->get_setting_id( $id ) . '" value="1" ' . $checked . '/>' . "\n";
		
		echo $html;
	}
			
	/**
	 * 
	 * Method to output text without any inputs
	 * 
	 * @param 
	 * @since 1.0.0
	 */
	public function description( $text  = '', $classes = null ) {
		
		if( $classes === null ) {
			$classes = 'description';
		}
		
		$html = '<p class="' . esc_html( $classes ) . '">' . $text . '</p>';
		echo $html;
	}

	/**
	 * 
	 * Method to output radio inputs
	 * 
	 * @param array $args
	 * @since 1.0.0
	 */
	public function radio_callback( $args ) {
		foreach ( $args['options'] as $key => $option ) {
			$checked = false;
			if ( isset( self::$settings->saved_settings[ $args['id'] ] ) && self::$settings->saved_settings[ $args['id'] ] == $key ) {
				$checked = true;
			} elseif ( isset( $args['std'] ) && $args['std'] == $key && ! isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
				$checked = true;
			}
			echo '<input name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
			echo '<label for="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		}
		echo '<p class="description">' . $args['desc'] . '</p>';
	}

	/**
	 * 
	 * Method to output multiple checkbox option inputs
	 * This is for a setting that there are multiple checkbox type options but they need to be tied to the same setting name
	 * 
	 * @param array $args
	 * @since 1.0.0
	 */
	public function multicheck_callback( $args ) {
		// Return empty string if no options.
		if ( empty( $args['options'] ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				echo '<p><strong>Warning:</strong> You have not included in options for this multiple checkbox setting.</p>';
			} else {
				echo '';
			}

			return;
		}
		$html = "\n";
		foreach ( $args['options'] as $key => $option ) {
			if ( isset( self::$settings->saved_settings[ $args['id'] ][ $key ] ) ) { 
				$enabled = $option; 
			} else { 
				$enabled = NULL; 
			}

			$html .= '<label for="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . '][' . $key . ']">';
			$html .= '<input name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . '][' . $key . ']" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked( $option, $enabled, false ) . '/>' . "\n";
			$html .= $option . '</label>';
		}
		// Render and style description text underneath if it exists.
		if ( ! empty( $args['desc'] ) ) {
			$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
		}
		echo $html;
	}

	/**
	 * 
	 * Method to output select box inputs
	 * 
	 * @param array $args
	 * @since 1.0.0
	 */
	public function select_callback( $args ) {
		// Return empty string if no options.
		if ( empty( $args['options'] ) ) {
			if( current_user_can( 'manage_options' ) ) {
				echo '<p><strong>Warning:</strong> You have not included in options for this select setting.</p>';
			} else {
				echo '';
			}

			return;
		}
		$html = "\n" . '<select id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']"/>' . "\n";
		foreach ( $args['options'] as $option => $name ) {
			$selected = isset( self::$settings->saved_settings[ $args['id'] ] ) ? selected( $option, self::$settings->saved_settings[ $args['id'] ], false ) : '';
			$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>' . "\n";
		}
		$html .= '</select>' . "\n";
		// Render and style description text underneath if it exists.
		if ( ! empty( $args['desc'] ) ) {
			$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
		}
		echo $html;
	}

	/**
	 * 
	 * Method to output textarea inputs
	 * 
	 * @param array $args
	 * @since 1.0.0
	 */
	public function textarea_callback( $args ) {
		if ( isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
			$value = self::$settings->saved_settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
		// Ignoring size at the moment.
		$html = "\n" . '<textarea class="large-text" cols="50" rows="10" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']">' . esc_textarea( $value ) . '</textarea>' . "\n";
		// Render and style description text underneath if it exists.
		if ( ! empty( $args['desc'] ) ) {
			$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
		}
		echo $html;
	}

	/**
	 * 
	 * Method to output number (HTML5) inputs
	 * 
	 * @param array $args
	 * @since 1.0.0
	 */
	public function number_callback( $args ) {
		if ( isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
			$value = self::$settings->saved_settings[ $args['id'] ];
		} else {
			$value = isset( $args['std'] ) ? $args['std'] : '';
		}
		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = "\n" . '<input type="number" class="' . $size . '-text" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" step="1" value="' . esc_attr( $value ) . '"/>' . "\n";
		// Render description text directly to the right in a label if it exists.
		if ( ! empty( $args['desc'] ) ) {
			$html .= '<label for="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";
		}
		echo $html;
	}
	
}
