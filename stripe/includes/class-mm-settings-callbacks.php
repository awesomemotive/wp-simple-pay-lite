<?php


if( ! class_exists( 'MM_Settings_Callbacks' ) ) {
	
	class MM_Settings_Callbacks {
		
		// We make these static so they don't constantly get overwritten
		protected static $settings;
		private static $children = array();
		
		public static $class_version = '1.0.0';
		
		/*
		 * Constructor
		 * 
		 * Needs a main settings object passed in
		 * We then set this classes settings to the passed in object so we can use the parent settings
		 */
		public function __construct( MM_Settings $settings ) {
			self::$settings = $settings;
		}
		
		/*
		 * Used by children of this class to make themselves "visible" from this class
		 */
		public function add_child( $child ) {
			self::$children[] = $child;
		}
		
		// Default callbacks
		
		public function text_callback( $args ) {
			
			if ( isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
				$value = self::$settings->saved_settings[ $args['id'] ];
			} else {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}

			$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '';
			$html = "\n" . '<input type="text" class="' . $size . '" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";

			// Render and style description text underneath if it exists.
			if ( ! empty( $args['desc'] ) ) {
				$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
			}

			echo $html;
		}
		
		public function checkbox_callback( $args ) {
			$checked = ( isset( self::$settings->saved_settings[$args['id']] ) ? checked( 1, self::$settings->saved_settings[$args['id']], false ) : '' );

			$html = "\n" . '<input type="checkbox" id="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" name="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

			// Render description text directly to the right in a label if it exists.
			if ( ! empty( $args['desc'] ) ) {
				$html .= '<label for="' . $args['prefix'] . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";
			}

			echo $html;
		}
		
		public function section_callback( $args ) {
			$html = '';

			if ( ! empty( $args['desc'] ) ) {
				$html .= $args['desc'];
			}

			echo $html;
		}
		
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
		
		/*
		 * Method used to call any child method callbacks or to output a default message
		 * if there are no callbacks found
		 */
		public function missing_callback( $args ) {
			
			// Indicator for our default output later if we need it
			$has_child_method = false;
			
			// There is a child class
			if( ! empty( self::$children ) ) {
				
				// Loop through children and load their callback methods if they exist
				foreach( self::$children as $child ) {
					if( method_exists( $child, $args['callback'] ) ) {
						
						// The child class has a method found so let's set this to true
						$has_child_method = true;
						
						// Create a new child class object and call the callback method
						$child = new $child;
						$child->$args['callback']( $args );
					}
				}
			}
			
			if( ! $has_child_method ) {
				echo '<p>There is no callback defined for: <strong>' . $args['callback'] . '</strong></p>';
			}
		}
	}
}
