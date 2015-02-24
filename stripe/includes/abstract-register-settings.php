<?php


abstract class Settings_Callbacks {
	
	//abstract public function text_callback( $args );
	abstract public function toggle_control_callback( $args );
	abstract public function checkbox_callback( $args );
	abstract public function section_callback( $args );
	abstract public function radio_callback( $args );
	
	
	public function missing_callback() {
		echo get_called_class() . '<br>';
		echo '<p>This callback is missing from the MM_Settings class</p>';
	}
	
}
