<?php


if( ! class_exists( 'MM_Settings_Callbacks' ) ) {
	
	class MM_Settings_Callbacks {
		
		protected static $settings;
		
		private static $children = array();
		
		public function __construct( MM_Settings $settings ) {
			//echo ' hit 1<br>';
			self::$settings = $settings;
			//echo '<pre>' . print_r( self::$settings, true ) . '</pre>';
		}
		
		public function get_settings() {
			//echo 'hit 2<br>';
			//echo '<pre>Settings:<br>' . print_r( self::$settings, true ) . '</pre>';
			return self::$settings;
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
		
		
		// TODO: This method is still specific to SC so it needs to be reworked.
		public function license_callback( $args ) {

			if ( isset( self::$settings->saved_settings[ $args['id'] ] ) ) {
				$value = self::$settings->saved_settings[ $args['id'] ];
			} else {
				$value = isset( $args['std'] ) ? $args['std'] : '';
			}

			$item = '';

			$html  = '<div class="license-wrap">';

			$size  = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular-text';
			$html .= "\n" . '<input type="text" class="' . $size . '" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";


			$licenses = get_option( 'sc_licenses' );


			// Add button on side of input
			if( ! empty( $licenses[ $args['product'] ] ) && $licenses[ $args['product'] ] == 'valid' && ! empty( $value ) ) {
				$html .= '<button class="button" data-sc-action="deactivate_license" data-sc-item="' .
						 ( ! empty( $args['product'] ) ? $args['product'] : 'none' ) . '">' . __( 'Deactivate', 'sc' ) . '</button>';
			} else {
				$html .= '<button class="button" data-sc-action="activate_license" data-sc-item="' .
						 ( ! empty( $args['product'] ) ? $args['product'] : 'none' ) . '">' . __( 'Activate', 'sc' ) . '</button>';
			}

			$license_class = '';
			$valid_message = '';
			
			// TODO: Add class methods for license checking?
			$valid = sc_check_license( $value, $args['product'] );

			if( $valid == 'valid' ) {
				$license_class = 'sc-valid';
				$valid_message = __( 'License is valid and active.', 'sc' );
			} else if( $valid == 'notfound' ) {
				$license_class = 'sc-invalid';
				$valid_message = __( 'License service could not be found. Please contact support for assistance.', 'sc' );
			} else {
				$license_class = 'sc-inactive';
				$valid_message = __( 'License is inactive.', 'sc' );
			}

			$html .= '<span class="sc-spinner-wrap"><span class="spinner sc-spinner"></span></span>';
			$html .= '<span class="sc-license-message ' . $license_class . '">' . $valid_message . '</span>';

			// Render and style description text underneath if it exists.
			if ( ! empty( $args['desc'] ) ) {
				$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";
			}

			$html .= '</div>';

			echo $html;
		}
		
		public function missing_callback( $args ) {
			//echo '<pre>' . print_r( self::$children, true ) . '</pre>';
			
			$has_child_method = false;
			
			if( ! empty( self::$children ) ) {
				
				foreach( self::$children as $child ) {
					if( method_exists( $child, $args['callback'] ) ) {
						
						$has_child_method = true;
						
						$child = new $child;
						// TODO: Need to figure out a way to generically check for a callback since we won't know it's name
						$child->$args['callback']( $args );
					}
				}
			}
			
			if( ! $has_child_method ) {
				echo '<p>This callback is missing from the MM_Settings class</p>';
			}
		}
		
		
		public function add_child( $child ) {
			self::$children[] = $child;
			
			//echo '<pre>' . print_r( self::$children, true ) . '</pre>';
		}
	}
}
