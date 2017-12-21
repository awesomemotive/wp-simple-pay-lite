<?php

namespace SimplePay\Core;

use SimplePay\Core\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installation.
 *
 * Static class that deals with plugin activation and deactivation events.
 *
 * @since 3.0.0
 */
class Installation {

	/**
	 * What happens when the plugin is activated.
	 *
	 * @since 3.0.0
	 */
	public static function activate() {

		update_option( 'simpay_dismiss_ssl', false );

		self::create_pages();
		self::create_options();

		do_action( 'simpay_activated' );
	}

	/**
	 * What happens when the plugin is deactivated.
	 *
	 * @since 3.0.0
	 */
	public static function deactivate() {

		do_action( 'simpay_deactivated' );
	}

	/**
	 * Create the pages for success and failure redirects
	 */
	public static function create_pages() {

		$options = get_option( 'simpay_settings' );

		if ( false === $options || ! array_key_exists( 'confirmation_pages', $options ) ) {

			$charge_confirmation = wp_insert_post( array(
				'post_title'     => __( 'Payment Confirmation', 'stripe' ),
				'post_content'   => '[simpay_payment_receipt]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed',
			) );

			$charge_failed = wp_insert_post( array(
				'post_title'     => __( 'Payment Failed', 'stripe' ),
				/* translators: %s: The [simpay_errors] shortcode */
				'post_content'   => sprintf( __( "%sWe're sorry, but your transaction failed to process. Please try again or contact site support.", 'stripe' ), '[simpay_error show_to="admin"]' . "\n\n" ),
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed',
			) );

			$options['confirmation_pages'] = array(
				'confirmation' => $charge_confirmation,
				'failed'       => $charge_failed,
			);
		}

		update_option( 'simpay_settings', $options );
	}

	/**
	 * Sets the default options.
	 *
	 * @since 3.0.0
	 */
	public static function create_options() {

		$default         = array();
		$page            = 'settings';
		$settings_pages  = new Pages( $page );
		$plugin_settings = $settings_pages->get_settings();

		if ( $plugin_settings && is_array( $plugin_settings ) ) {

			foreach ( $plugin_settings as $id => $settings ) {

				$group = 'simpay_' . $page . '_' . $id;

				if ( isset( $settings['sections'] ) ) {

					if ( $settings['sections'] && is_array( $settings['sections'] ) ) {

						foreach ( $settings['sections'] as $section_id => $section ) {

							if ( isset( $section['fields'] ) ) {

								if ( $section['fields'] && is_array( $section['fields'] ) ) {

									foreach ( $section['fields'] as $key => $field ) {

										if ( isset ( $field['type'] ) ) {
											// Maybe an associative array.
											if ( is_int( $key ) ) {
												$default[ $section_id ] = self::get_field_default_value( $field );
											} else {
												$default[ $section_id ][ $key ] = self::get_field_default_value( $field );
											}
										}

									} // Loop fields.

								} // Are fields non empty?

							} // Are there fields?

						} // Loop fields sections.

					} // Are sections non empty?

				} // Are there sections?

				add_option( $group, $default, '', true );

				// Reset before looping next settings page.
				$default = array();
			}

		}
	}

	/**
	 * Get field default value.
	 *
	 * Helper function to set the default value of a field.
	 *
	 * @since  3.0.0
	 *
	 * @param  $field
	 *
	 * @return mixed
	 */
	private static function get_field_default_value( $field ) {

		$saved_value   = isset( $field['value'] ) ? $field['value'] : '';
		$default_value = isset( $field['default'] ) ? $field['default'] : '';

		return ! empty( $saved_value ) ? $saved_value : $default_value;
	}

}
