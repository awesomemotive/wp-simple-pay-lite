<?php


if( ! class_exists( 'MM_Settings_Callbacks' ) ) {
	
	class MM_Settings_Callbacks extends MM_Settings {
		
		public function __construct() {
			//echo '<pre>' . print_r( $this->saved_settings, true ) . '</pre>';
			//$this->saved_settings = parent::get_settings();
			//echo 'PREFIX: ' . $this->prefix;
		}
		
		// Callback functions
		public function toggle_control_callback( $args ) {
			
			$checked = ( isset( $this->saved_settings[$args['id']] ) ? checked( 1, $this->saved_settings[$args['id']], false ) : '' );

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

		public function text_callback( $args ) {

			if ( isset( $this->saved_settings[ $args['id'] ] ) )
				$value = $this->saved_settings[ $args['id'] ];
			else
				$value = isset( $args['std'] ) ? $args['std'] : '';

			$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '';
			$html = "\n" . '<input type="text" class="' . $size . '" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="' . trim( esc_attr( $value ) ) . '"/>' . "\n";

			// Render and style description text underneath if it exists.
			if ( ! empty( $args['desc'] ) )
				$html .= '<p class="description">' . $args['desc'] . '</p>' . "\n";

			echo $html;
		}

		public function checkbox_callback( $args ) {
			
			$checked = ( isset( $this->saved_settings[$args['id']] ) ? checked( 1, $this->saved_settings[$args['id']], false ) : '' );

			$html = "\n" . '<input type="checkbox" id="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" value="1" ' . $checked . '/>' . "\n";

			// Render description text directly to the right in a label if it exists.
			if ( ! empty( $args['desc'] ) )
				$html .= '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . ']"> '  . $args['desc'] . '</label>' . "\n";

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

				if ( isset( $this->saved_settings[ $args['id'] ] ) && $this->saved_settings[ $args['id'] ] == $key ) {
					$checked = true;
				} else if( isset( $args['std'] ) && $args['std'] == $key && ! isset( $this->saved_settings[ $args['id'] ] ) ) {
					$checked = true;
				}

				echo '<input name="sc_settings_' . $args['section'] . '[' . $args['id'] . ']" id="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
				echo '<label for="sc_settings_' . $args['section'] . '[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
			}

			echo '<p class="description">' . $args['desc'] . '</p>';
		}
		
		
		// TODO: This method is still specific to SC so it needs to be reworked.
		public function license_callback( $args ) {

			if ( isset( $this->saved_settings[ $args['id'] ] ) ) {
				$value = $this->saved_settings[ $args['id'] ];
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
		
		public function missing_callback() {
			echo '<p>This callback is missing from the MM_Settings class</p>';
		}
		
	}
}
