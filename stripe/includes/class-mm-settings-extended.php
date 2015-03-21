<?php


class MM_Settings_Extended extends MM_Settings_Output {
	
	public function __construct( $option ) {
		parent::__construct( $option );
	}
	
	public function toggle_control( $id, $classes = '' ) {
		global $sc_options;
	
		//$checked = ( isset( $sc_options[$args['id']] ) ? checked( 1, $sc_options[$args['id']], false ) : '' );
		
		$value = $this->get_setting_value( $id );
		
		$checked = ( ! empty( $value ) ? checked( 1, $value, false ) : '' );

		$html = '<div class="' . $this->option . '-toggle-switch-wrap">
				<label class="switch-light switch-candy switch-candy-blue" onclick="">
					<input type="checkbox" id="' . $this->get_setting_id( $id ) . '" name="' . $this->get_setting_id( $id ) . '" value="1" ' . $checked . '/>
					<span>
					  <span>' . __( 'Test', 'sc' ) . '</span>
					  <span>' . __( 'Live', 'sc' ) . '</span>
					</span>
					<a></a>
				</label></div>';

		echo $html;
	}
}
