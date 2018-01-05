<?php

namespace SimplePay\Core\Payments;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Payment Details Class
 *
 * Handles all of the payment details output after a transaction.
 */
class Details {

	public $payment = null;

	// The available tags that can be used in the Payment Confirmation text editor setting
	public $tags = array();

	/**
	 * Details constructor.
	 *
	 * @param Payment $payment The Payment object to associate this object with
	 */
	public function __construct( Payment $payment ) {

		// Set our class payment variable to the Payment object passed in
		$this->payment = $payment;
	}

	/**
	 * Setup all the tags available in the Payment Confirmation editor
	 */
	public function set_tags() {

		global $simpay_form;

		$charge_date = '';

		// Set the date the charge was created. Allow filter.
		if ( isset( $this->payment->charge->created ) ) {
			$charge_date = date_i18n( get_option( 'date_format' ), $this->payment->charge->created );
		}

		// Filtered array of all the available tags
		// type is the type of details screen it can be shown on: all = all screens, subscription = only subscription details, etc as we add more
		// value is the data that this tag will be replaced with
		$this->tags = apply_filters( 'simpay_payment_details_template_tags', array(
			'charge-id' => array(
				'type'  => array( 'all' ),
				'value' => isset( $this->payment->charge->id ) ? $this->payment->charge->id : '',
			),

			'charge-date' => array(
				'type'  => array( 'all' ),
				'value' => apply_filters( 'simpay_details_order_date', $charge_date ),
			),

			'company-name' => array(
				'type'  => array( 'all' ),
				'value' => isset( $this->payment->company_name ) ? $this->payment->company_name : '',
			),

			'item-description' => array(
				'type'  => array( 'all' ),
				'value' => isset( $simpay_form->item_description ) ? $simpay_form->item_description : '',
			),

			'total-amount' => array(
				'type'  => array( 'all' ),
				'value' => isset( $this->payment->charge->amount ) ? simpay_formatted_amount( $this->payment->charge->amount, $this->payment->get_currency() ) : '',
			),
		), $this->payment );
	}

	/**
	 * Function to return or echo the finalized HTML output for the details.
	 *
	 * @param bool $echo Whether or not to output the string or return it
	 *
	 * @return string The string if it wasn't output
	 */
	public function html( $echo = true ) {

		// Setup all of our tags
		$this->set_tags();

		$html = $this->process_template_tags( apply_filters( 'simpay_process_template_tag_type', 'one_time' ) );

		// Use wpautop since we are pulling the content from a text editor
		$html = wpautop( $html );

		// Add filters to allow modification of opening and closing HTML elements around the payment details content
		$before_html = apply_filters( 'simpay_before_payment_details', '<div class="simpay-payment-receipt-wrap">' );
		$after_html  = apply_filters( 'simpay_after_payment_details', '</div>' );

		// Clear out session vars here.
		// Shortcodes->print_payment_details() will have been processed by this point.
		\SimplePay\Core\SimplePay()->session->clear();

		// Determine if we need to echo the output or just return it
		if ( $echo ) {
			echo $before_html . $html . $after_html;
		} else {
			return $before_html . $html . $after_html;
		}

		return '';
	}

	/**
	 * Process the template tags
	 *
	 * @param string $type The type of tags to process.
	 *
	 * @return string The output of the converted values
	 */
	public function process_template_tags( $type = '' ) {

		// Get the editor content we need to sift through
		$details = $this->get_editor_content( $type );


		if ( ! empty( $this->tags ) && is_array( $this->tags ) ) {
			// Loop through all of our tags and replace the tags with the appropriate data
			foreach ( $this->tags as $k => $v ) {

				// Check if the type of output is suitablt for 'all' types of detail screens or just the specified type that was sent in as a parameter (i.e. 'subscription')
				// If neither then replace the tag with an empty space so the tag doesn't get output to the screen as plain text.
				if ( in_array( 'all', $v['type'] ) || in_array( $type, $v['type'] ) ) {
					$details = str_replace( '{' . $k . '}', $v['value'], $details );
				} else {
					$details = str_replace( '{' . $k . '}', '', $details );
				}
			}
		}

		return $details;
	}

	/**
	 * Get the content of a specific wp_editor from the global settings
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_editor_content( $type ) {

		$display_options = get_option( 'simpay_settings_display' );

		if ( false === $display_options ) {
			return '';
		}

		switch ( $type ) {
			case 'one_time':
				return isset( $display_options['payment_confirmation_messages']['one_time_payment_details'] ) ? $display_options['payment_confirmation_messages']['one_time_payment_details'] : simpay_get_editor_default( 'one_time' );
			case has_filter( 'simpay_get_editor_content' ):
				return apply_filters( 'simpay_get_editor_content', '', $type, $display_options );
			default:
				return '';
		}
	}
}
