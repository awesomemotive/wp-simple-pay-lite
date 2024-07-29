<?php
/**
 * Shortcodes
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

use SimplePay\Core\Payments\Payment_Confirmation;
use SimplePay\Core\Settings;
use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core Shortcodes Class
 *
 * Register and handle custom shortcodes.
 */
class Shortcodes {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Add shortcodes.
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes() {

		add_shortcode( 'simpay', array( $this, 'print_public_form' ) );
		add_shortcode( 'simpay_payment_receipt', array( $this, 'print_payment_receipt' ), 10, 2 );

		// Deprecated.
		add_shortcode( 'simpay_error', '__return_empty_string' );
		add_shortcode( 'simpay_preview', '__return_empty_string' );

		do_action( 'simpay_add_shortcodes' );
	}

	/**
	 * Shortcode to render public paymetn form
	 *
	 * @since 3.0.0
	 *
	 * @param array $attributes Shortcode attributes.
	 * @return string
	 */
	public function print_public_form( $attributes ) {
		$args = shortcode_atts(
			array(
				'id'            => null,
				'instanceid'    => null,
				'isbuttonblock' => '0',
			),
			$attributes
		);

		$args['isbuttonblock'] = (bool) $args['isbuttonblock'];

		$id   = absint( $args['id'] );
		$html = '';

		if ( $id > 0 ) {

			$form_post = get_post( $id );

			if ( ! $form_post ) {
				return '';
			}

			$html .= self::form_html( $id, $args );
		}

		return $html;
	}

	/**
	 * Private function for returning payment form html common between public & preview modes.
	 *
	 * @since 3.0.0
	 *
	 * @param int                  $form_id Payment Form ID.
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return string
	 */
	private function form_html( $form_id, $atts ) {
		if ( false === simpay_is_rest_api_enabled() ) {
			return wpautop(
				esc_html__(
					'WP Simple Pay requires the WordPress REST API to be enabled to process payments.',
					'stripe'
				)
			);
		}

		if ( ! isset( $_GET['simpay-preview'] ) && 'publish' !== get_post_status( $form_id ) ) {
			if ( current_user_can( 'manage_options' ) ) {
				return wp_kses_post(
					wpautop(
						sprintf(
							/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
							__( 'This payment form is currently unpublished. %1$sPreview payment form →%2$s.', 'stripe' ),
							'<a href="' . esc_url( get_preview_post_link( $form_id ) ) . '">',
							'</a>'
						)
					)
				);
			} else {
				return '';
			}
		}

		$has_keys = simpay_check_keys_exist();

		// Show a notice to admins if they have not setup Stripe.
		if ( ! $has_keys && current_user_can( 'manage_options' ) ) {
			$stripe_account_settings_url = Settings\get_url(
				array(
					'section'    => 'stripe',
					'subsection' => 'account',
				)
			);

			return wp_kses_post(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__( 'Please complete your %1$sStripe Setup%2$s to view the payment form.', 'stripe' ),
					'<a href="' . esc_url( $stripe_account_settings_url ) . '">',
					'</a>'
				)
			);
			// Show nothing to guests if Stripe is not setup.
		} elseif ( ! $has_keys && ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		try {
			/** @var \SimplePay\Core\Abstracts\Form $form */
			$form = simpay_get_form( $form_id );

			if ( false !== $form ) {
				$prices = simpay_get_payment_form_prices( $form );

				if ( empty( $prices ) ) {
					if ( current_user_can( 'manage_options' ) ) {
						$edit_prices_url = add_query_arg(
							array(
								'post'   => $form->id,
								'action' => 'edit',
							),
							admin_url( 'post.php' )
						);

						return wp_kses_post(
							sprintf(
								/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
								__( 'Attention: The payment form does not have prices configured. %1$sAdd price options%2$s to collect payments.', 'stripe' ),
								'<a href="' . esc_url( $edit_prices_url ) . '#payment-options-settings-panel">',
								'</a>'
							)
						);
					}

					return;
				}

				ob_start();

				// Check schedule for output.
				if ( false === $form->has_available_schedule() ) {
					$out_of_schedule_message = sprintf(
						/* translators: Form title */
						__(
							'Sorry, "%s" is not available for purchase.',
							'stripe'
						),
						esc_html( $form->company_name )
					);

					/**
					 * Filters the out of schedule message.
					 *
					 * @since 4.6.4
					 *
					 * @param string                         $out_of_stock_message Out of stock message.
					 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
					 */
					$out_of_schedule_message = apply_filters(
						'simpay_form_out_of_schedule_message',
						$out_of_schedule_message,
						$form
					);

					echo wp_kses_post( wpautop( $out_of_schedule_message ) );

					// Check inventory for output.
				} elseif ( false === $form->has_available_inventory() ) {
					$out_of_stock_message = sprintf(
						/* translators: Form title */
						__(
							'Sorry, "%s" is not available for purchase.',
							'stripe'
						),
						esc_html( $form->company_name )
					);

					/**
					 * Filters the out of stock message.
					 *
					 * @since 4.6.4
					 *
					 * @param string                         $out_of_stock_message Out of stock message.
					 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
					 */
					$out_of_stock_message = apply_filters(
						'simpay_form_out_of_stock_message',
						$out_of_stock_message,
						$form
					);

					echo wp_kses_post( wpautop( $out_of_stock_message ) );

					// Output.
				} else {
					$form->html();

					if ( true === $atts['isbuttonblock'] ) {
						$this->print_button_block_script( $form_id, $atts );
					}
				}

				return ob_get_clean();
			} else {
				return '';
			}
		} catch ( \Exception $e ) {
			return Utils\handle_exception_message( $e );
		}
	}

	/**
	 * Prints payment details.
	 *
	 * Since 3.6.0 the shortcode can be a wrapped shortcode with content inside
	 * that will be parsed like the content in Settings > Payment Confirmation.
	 *
	 * @todo Maybe split out some of grunt work involved in collecting the necessary information.
	 * @todo Try to expand any available objects to reduce extra API calls.
	 *
	 * @since 3.0.0
	 *
	 * @param array  $atts Shortcode attributes.
	 * @param string $content Shortcode content.
	 * @return string
	 */
	public function print_payment_receipt( $atts = array(), $content = '' ) {
		$atts = shortcode_atts(
			array(),
			$atts,
			'simpay_payment_confirmation'
		);

		$payment_confirmation_data = array();

		try {
			$payment_confirmation_data = Payment_Confirmation\get_confirmation_data();

			if ( ! isset( $payment_confirmation_data['customer'] ) ) {
				return Payment_Confirmation\get_error();
			}

			if ( empty( $payment_confirmation_data ) ) {
				return Payment_Confirmation\get_error();
			}

			// Retrieve default content if nothing has been passed to the shortcode.
			if ( '' === $content ) {
				$content = Payment_Confirmation\get_content();
			}

			// If we have a form, and there is custom content, use it.
			// Use a late priority to override content pulled from Pro.
			$form_id = isset( $_GET['form_id'] )
				? absint( $_GET['form_id'] )
				: 0;

			$form = simpay_get_form( $form_id );

			if ( false !== $form ) {
				$form_message = $form->get_confirmation_message();

				if ( ! empty( $form_message ) ) {
					add_filter(
						'simpay_payment_confirmation_content',
						function () use ( $form_message ) {
							return $form_message;
						},
						99
					);
				}
			}

			/**
			 * Filters the content of the confirmation shortcode.
			 *
			 * This allows different form types to parse the confirmation smart tags differently.
			 *
			 * @since 3.6.0
			 *
			 * @param string $content Payment confirmation shortcode content.
			 * @param array  $payment_confirmation_data {
			 *   Contextual information about this payment confirmation.
			 *
			 *   @type \SimplePay\Vendor\Stripe\Customer               $customer Stripe Customer
			 *   @type \SimplePay\Core\Abstracts\Form $form Payment form.
			 *   @type object                         $subscriptions Subscriptions associated with the Customer.
			 *   @type object                         $paymentintents PaymentIntents associated with the Customer.
			 * }
			 */
			$content = apply_filters( 'simpay_payment_confirmation_content', $content, $payment_confirmation_data );

			$content = Payment_Confirmation\Template_Tags\parse_content( $content, $payment_confirmation_data );

			// Perform actions when the [simpay_payment_receipt] shortcode is
			// parsed and output successfully.
			//
			// Multiple actions are called. View the function for more details.
			Payment_Confirmation\do_confirmation_actions(
				$payment_confirmation_data
			);

			// Processing a SEPA Direct Debit Subscription can have a slight delay which
			// can cause a RateLimitException to be thrown when trying to display the
			// Payment Confirmation message content. Refresh the page automatically.
		} catch ( \SimplePay\Vendor\Stripe\Exception\RateLimitException $e ) {
			$content = esc_html__(
				'Your payment is still processing. This page will reload in 5 seconds&hellip;',
				'stripe'
			);

			$content .= '<script>setInterval( function() { window.location.reload(); }, 5000 );</script>';
		} catch ( \Exception $e ) {
			if ( current_user_can( 'manage_options' ) ) {
				$content = Utils\handle_exception_message( $e );
			} else {
				$content = Payment_Confirmation\get_error();
			}
		}

		/**
		 * Filters the HTML output before the payment confirmation.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 Pass payment confirmation data.
		 *
		 * @param string
		 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
		 */
		$before_html = apply_filters(
			'simpay_before_payment_details',
			'<div class="simpay-payment-receipt-wrap">',
			$payment_confirmation_data
		);

		/**
		 * Filters the HTML output after the payment confirmation.
		 *
		 * @since 3.0.0
		 * @since 3.7.0 Pass payment confirmation data.
		 *
		 * @param string
		 * @param array  $payment_confirmation_data Array of data to send to the Payment Confirmation smart tags.
		 */
		$after_html = apply_filters(
			'simpay_after_payment_details',
			'</div>',
			$payment_confirmation_data
		);

		$content = wpautop(
			do_shortcode( $content )
		);

		return $before_html . $content . $after_html;
	}

	/**
	 * Prints the inline script to launch a payment form from a core button block.
	 *
	 * @since 4.4.7
	 *
	 * @param int                  $form_id Payment form ID.
	 * @param array<string, mixed> $atts Shortcode attributes.
	 * @return void
	 */
	private function print_button_block_script( $form_id, $atts ) {
		printf(
			'<script>
				( function( $ ) {
					$( \'#simpay-block-button-%1$s\' ).parent().find( \'.wp-block-button__link\' ).click( function( e ) {
						e.preventDefault();
						$( this ).addClass( \'is-busy\' );
						$( \'#simpay-block-button-%1$s .simpay-payment-btn\' )
							.click();
						$( \'#simpay-block-button-%1$s #simpay-modal-control-%2$d\' )
							.click();
					} );
				} )( jQuery );
			</script>',
			esc_js( $atts['instanceid'] ),
			esc_js( $form_id )
		);
	}
}
