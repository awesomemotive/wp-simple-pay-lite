<?php


if( ! class_exists( 'MM_Settings_Callbacks' ) ) {
	
	class MM_Settings_Callbacks {
		
		// We make these static so they don't constantly get overwritten
		protected static $settings;
		private static $children = array();
		
		/*
		 * Constructor
		 * 
		 * Needs a main settings object passed in
		 * We then set this classes settings to the passed in object so we can use the parent settings
		 */
		public function __construct( MM_Settings $settings ) {
			self::$settings = $settings;
		}
		
		// Callback functions
		public function toggle_control_callback( $args ) {
			
			$checked = ( isset( self::$settings->saved_settings[$args['id']] ) ? checked( 1, self::$settings->saved_settings[$args['id']], false ) : '' );

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

		//public function text_callback( $args ) {}

		/*public function checkbox_callback( $args ) {
			
			$checked = ( isset( self::$settings->saved_settings[$args['id']] ) ? checked( 1, self::$settings->saved_settings[$args['id']], false ) : '' );

			$html = "\n" . '<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

			// Render description text directly to the right in a label if it exists.
			if ( ! empty( $args['desc'] ) )
				$html .= '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";

			echo $html;
		}*/

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
				} else if( isset( $args['std'] ) && $args['std'] == $key && ! isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
					$checked = true;
				}

				echo '<input name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" id="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
				echo '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
			}

			echo '<p class="description">' . $args['desc'] . '</p>';
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
				echo '<p>This callback is missing from the MM_Settings class</p>';
			}
		}
		
		/*
		 * Used by children of this class to make themselves "visible" from this class
		 */
		public function add_child( $child ) {
			self::$children[] = $child;
		}
	}
}
