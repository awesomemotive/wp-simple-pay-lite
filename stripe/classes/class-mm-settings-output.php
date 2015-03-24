<?php

/**
 * Base settings output class - displays the fields HTML
 *
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'MM_Settings_Output' ) ) {
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
		public function radio_button( $id, $label, $value, $section = '' ) {

			$html = '';

			if( ! empty( $section ) ) {
				$id   = $this->get_setting_id( $id );
				$name = $this->option . '[' . $section . ']';

				$saved_value =  $this->get_setting_value( $section );

				$checked = $saved_value !== null ? ( ( $saved_value == $value ) ? true : false ) : false;

			} else {
				$id = $this->get_setting_id( $id );

				$checked = ( $this->get_setting_value( $id ) !== null ? true : false );
			}

			$html  = '<input name="' . $name . '" id="' . $id . '" type="radio" value="' . esc_attr( $value ) . '" ' . checked( true, $checked, false ) . '/>&nbsp;';
			$html .= '<label for="' . $id . '">' . $label . '</label><br/>';

			echo $html;
		}

		/**
		 * 
		 * Method to output select box inputs
		 * 
		 * @param array $args
		 * @since 1.0.0
		 */
		public function selectbox( $id, $options, $classes = '' ) {
			// Return empty string if no options.
			if ( empty( $options ) ) {
				if( current_user_can( 'manage_options' ) ) {
					echo '<p><strong>Warning:</strong> You have not included in options for this select setting.</p>';
				} else {
					echo '';
				}

				return;
			}

			$selected = $this->get_setting_value( $id ) !== null ? $this->get_setting_value( $id ) : '';

			$html = '<select id="' . $this->get_setting_id( $id ) . '" name="' . $this->get_setting_id( $id )  . '" />' . "\n";

			foreach ( $options as $option ) {
				$html .= '<option value="' . $option . '" ' . selected( $option, $selected, false ) . '>' . $option . '</option>' . "\n";
			}

			$html .= '</select>' . "\n";

			echo $html;
		}

		/**
		 * 
		 * Method to output textarea inputs
		 * 
		 * @param array $args
		 * @since 1.0.0
		 */
		public function textarea( $id, $classes = '' ) {
			if ( $this->get_setting_value( $id ) !== null ) {
				$value = $this->get_setting_value( $id );
			} else {
				$value = '';
			}

			// Ignoring size at the moment.
			$html = '<textarea class="large-text" cols="50" rows="10" id="' . $this->get_setting_id( $id ) . '" name="' . $this->get_setting_id( $id ) . '">' . esc_textarea( $value ) . '</textarea>' . "\n";

			echo $html;
		}

		/**
		 * 
		 * Method to output number (HTML5) inputs
		 * 
		 * @param array $args
		 * @since 1.0.0
		 */
		public function number( $id, $classes = '' ) {

			if ( $this->get_setting_id( $id ) !== null ) {
				$value = $this->get_setting_value( $id );
			} else {
				$value = '';
			}

			if( empty( $classes ) ) {
				$classes = 'regular-text';
			}

			$html = '<input type="number" class="' . esc_attr( $classes ) . '" id="' . $this->get_setting_id( $id ) . '" name="' . $this->get_setting_id( $id ) . '" step="1" value="' . esc_attr( $value ) . '"/>' . "\n";

			echo $html;
		}
	}
}
