<?php

/**
 * Settings extension for additional controls not available in the base class
 *
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'MM_Settings_Extended' ) ) {
	class MM_Settings_Extended extends MM_Settings_Output {

		public function __construct( $option ) {
			parent::__construct( $option );
		}

		public function toggle_control( $id, $options, $classes = null ) {

			if( count( $options ) != 2 ) {
				echo __( 'You must include 2 options for a toggle switch!', 'sc' ) . '<br>';
				return;
			}

			if( $classes === null ) {
				$classes = 'switch-light switch-candy switch-candy-blue';
			}

			$value = $this->get_setting_value( $id );

			$checked = ( ! empty( $value ) ? checked( 1, $value, false ) : '' );

			$html  = '<div class="' . esc_attr( $this->option ) . '-toggle-switch-wrap">';
			$html .= '<label class="' . esc_attr( $classes ) . '">';
			$html .= '<input type="checkbox" id="' . esc_attr( $this->get_setting_id( $id ) ) . '" name="' . esc_attr( $this->get_setting_id( $id ) ) . '" value="1" ' . $checked . '/>';
			$html .= '<span>';

			foreach( $options as $o ) {
				$html .= '<span>' . esc_html( $o ) . '</span>';
			}

			$html .= '</span>';
			$html .= '<a></a>';
			$html .= '</label></div>';

			echo $html;
		}
	}
}
