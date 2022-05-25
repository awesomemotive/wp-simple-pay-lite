<?php
/**
 * Install
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

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

		// Record installation time.
		add_option( 'simpay_installed', time() );

		// Record the start of the customer journey.
		//
		// This is separate from simpay_installed to ensure we can accurately
		// calculate the length between the start of the journey and an achievement.
		// Using simpay_installed would be innacurate as it was set when the plugin
		// was updated to 4.4.1.
		add_option( 'simpay_customer_journey_start', time() );

		self::create_pages();

		// Set activation redirect transient.
		set_transient(
			'simpay_activation_redirect',
			true,
			( MINUTE_IN_SECONDS * 5 )
		);

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
		// Payment Confirmation: Success.
		$success_page = simpay_get_setting( 'success_page', '' );

		if ( empty( $success_page ) ) {
			$success_page = wp_insert_post(
				array(
					'post_title'     => __( 'Payment Confirmation', 'stripe' ),
					'post_content'   => '<!-- wp:shortcode -->[simpay_payment_receipt]<!-- /wp:shortcode -->',
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);

			if ( ! is_wp_error( $success_page ) ) {
				simpay_update_setting( 'success_page', $success_page );
			}
		}

		// Payment Confirmation: Failure/Cancelled.
		$failure_page = simpay_get_setting( 'failure_page', '' );

		if ( empty( $failure_page ) ) {
			$failure_page = wp_insert_post(
				array(
					'post_title'     => __( 'Payment Failed', 'stripe' ),
					'post_content'   => '<!-- wp:paragraph -->' . __(
						'We\'re sorry, but your transaction failed to process. Please try again or contact site support.',
						'stripe'
					) . '<!-- /wp:paragraph -->',
					'post_status'    => 'publish',
					'post_author'    => 1,
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);

			if ( ! is_wp_error( $failure_page ) ) {
				simpay_update_setting( 'failure_page', $failure_page );
				simpay_update_setting( 'cancelled_page', $failure_page );
			}
		}
	}
}
