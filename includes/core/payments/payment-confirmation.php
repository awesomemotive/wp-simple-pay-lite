<?php
/**
 * Payment receipt/confirmation functionality.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments\Payment_Confirmation;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves the default confirmation content set in Settings > Payment Confirmation.
 *
 * @since 3.6.0
 *
 * @param string $confirmation_type The type of confirmation content to retrieve.
 * @return string
 */
function get_content() {
	$display_options = get_option( 'simpay_settings_display' );
	$content         = simpay_get_editor_default( 'one_time' );

	if ( ! $display_options ) {
		return $content;
	}
	
	$content = isset( $display_options['payment_confirmation_messages']['one_time_payment_details'] ) ? 
		$display_options['payment_confirmation_messages']['one_time_payment_details'] :
		$content;

	/**
	 * @deprecated 3.6.0
	 */
	$content = apply_filters_deprecated(
		'simpay_get_editor_content',
		array(
			$content,
			'one_time',
			$display_options
		),
		'3.6.0',
		'simpay_payment_confirmation_content'
	);

	return $content;
}

/**
 * Creates a generic error message shown when the confirmation page is
 * reached but the relevant records are unable to be retrieved.
 *
 * @since 3.6.0
 *
 * @return string
 */
function get_error() {
	$message = wpautop( esc_html__( 'Unable to locate payment record.', 'stripe' ) );

	/**
	 * Filter the error message shown when a Payment Confirmation cannot be created.
	 * 
	 * @since unknown
	 *
	 * @param string Error message.
	 */
	return apply_filters( 'simpay_charge_error_message', $message );
}
