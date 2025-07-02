<?php
/**
 * Webhook Tool
 *
 * @since 4.14.0
 * @package SimplePay\Core\Webhook
 */

namespace SimplePay\Core\Webhook;

use SimplePay\Core\Utils;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * CreateWebhookTool class.
 *
 * @since 4.14.0
 */
class CreateWebhookTool implements SubscriberInterface {

	/**
	 * Webhook endpoint manager.
	 *
	 * @since 4.14.0
	 * @var \SimplePay\Core\Webhook\WebhookEndpointManager $endpoint_manager
	 */
	protected $endpoint_manager;

	/**
	 * StripeConnectCreation.
	 *
	 * @since 4.14.0
	 *
	 * @param \SimplePay\Core\Webhook\WebhookEndpointManager $endpoint Webhook endpoint manager.
	 * @return void
	 */
	public function __construct( WebhookEndpointManager $endpoint ) {
		$this->endpoint_manager = $endpoint;
	}


	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'__unstable_simpay_before_webhook_setting' => 'output_button',
			'wp_ajax_simpay_recreate_webhook'          => 'handle_recreate_webhook',
		);
	}

	/**
	 * Outputs the button to recreate the webhook.
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function output_button() {

		$webhooks_url = simpay_is_test_mode()
		? 'https://dashboard.stripe.com/test/workbench/webhooks/'
		: 'https://dashboard.stripe.com/workbench/webhooks/';

		// Check current webhook status.
		$endpoint       = $this->endpoint_manager->exists();
		$status_message = '';
		$status_color   = '';
		if ( $endpoint instanceof \SimplePay\Vendor\Stripe\WebhookEndpoint ) {
			if ( $this->endpoint_manager->is_valid( $endpoint ) ) {
				$status_message = __( 'Your Stripe webhook is set up correctly.', 'stripe' );
				$status_color   = 'green';
			} else {
				$status_message = __( 'Action required: Your Stripe webhook is not valid. Please recreate it.', 'stripe' );
				$status_color   = 'red';
			}

			$action_text = __( 'Recreate Webhook', 'stripe' );
		} else {
			$status_message = __( 'No Stripe webhook is currently set up.', 'stripe' );
			$status_color   = 'red';
			$action_text    = __( 'Create Webhook', 'stripe' );
		}

		// Output the button.

		echo wp_kses_post(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__( 'Stripe uses webhooks to notify WP Simple Pay when an event has occurred in your Stripe account. Ensure an endpoint is present in the %1$sStripe webhook settings %2$s', 'stripe' ),
				'<a href="' . $webhooks_url . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link"><strong>',
				'</strong>' . Utils\get_external_link_markup() . '</a><br><br>'
			)
		);
		wp_nonce_field( 'simpay_recreate_webhook', 'simpay_recreate_webhook_nonce' );

		echo '<div style="display: flex; align-items: center; gap: 10px;">';
			echo '<button type="button" id="simpay-recreate-webhook" class="button button-primary button-large">' . esc_html( $action_text ) . '</button>';

			echo '<div id="simpay-webhook-status-message" style="color:' . esc_attr( $status_color ) . ';">' . esc_html( $status_message ) . '</div>';
		echo '</div>';

		echo '<div id="simpay-recreate-webhook-message" style="margin-top:10px;"></div>';
	}

	/**
	 * Handles the recreate webhook request.
	 *
	 * @since 4.14.0
	 *
	 * @return void
	 */
	public function handle_recreate_webhook() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'stripe' ) ) );
		}

		if ( ! isset( $_POST['simpay_recreate_webhook_nonce'] ) || ! wp_verify_nonce( $_POST['simpay_recreate_webhook_nonce'], 'simpay_recreate_webhook' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce check failed. Please reload the page and try again.', 'stripe' ) ) );
		}

		$endpoint = $this->endpoint_manager->exists();

		if ( $endpoint instanceof \SimplePay\Vendor\Stripe\WebhookEndpoint && $this->endpoint_manager->is_valid( $endpoint ) ) {
			wp_send_json_success( array( 'message' => __( 'Webhook recreated.', 'stripe' ) ) );
		}

		$this->endpoint_manager->create();
		wp_send_json_success( array( 'message' => __( 'Webhook recreated.', 'stripe' ) ) );
	}
}
