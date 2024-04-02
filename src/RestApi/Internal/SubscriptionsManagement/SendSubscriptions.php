<?php
/**
 * Send subscriptions managment email to customer.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.8.0
 */

namespace SimplePay\Core\RestApi\Internal\SubscriptionsManagement;

use SimplePay\Core\Emails\Mailer;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\API\Subscriptions;
use SimplePay\Core\API\Customers;
use WP_REST_Response;
use WP_REST_Server;
use SimplePay\Core\RestApi\Internal\Utils\TokenValidationUtils;
/**
 * SendSubscriptions class.
 *
 * @since 4.8.0
 */
class SendSubscriptions implements SubscriberInterface {

	/**
	 * Email address of the customer associated with the class instance.
	 *
	 * @since 4.8.0
	 * @var string
	 */
	private $customer_email;

	/**
	 * Store ManageSubscriptionsEmail instance.
	 *
	 * @since 4.8.0
	 * @var mixed
	 */
	private $email;

	/**
	 * Set ManageSubscriptionsEmail instance.
	 *
	 * @since 4.8.0
	 * @param array<string, \SimplePay\Core\Emails\Email\EmailInterface> $email email template instance.
	 * @return void
	 */
	public function __construct( $email ) {
		$this->email = $email;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {

		return array(
			'rest_api_init' => 'register_route',
		);
	}

	/**
	 * Registers the REST API route for
	 * `[POST] /wpsp/__internal__/send/subscriptions`.
	 *
	 * @since 4.8.0
	 *
	 * @return void
	 */
	public function register_route() {

		register_rest_route(
			'wpsp/__internal__',
			'send/subscriptions',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_data' ),
					'permission_callback' => array( $this, 'can_request_for_data' ),
				),
			)
		);
	}

	/**
	 * Determines if the current user can request for subscription managment.
	 *
	 * @since 4.8.0
	 *
	 * @return bool
	 */
	public function can_request_for_data() {
		// TODO: A discussion needs to determine whether this API will be made public.
		return true;
	}

	/**
	 * Sends subscription management links to the customer's email.
	 *
	 * @since 4.8.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 *
	 * @return \WP_REST_Response The REST response containing the result of the operation.
	 */
	public function send_data( $request ) {

		// Initialize the result array.
		$result = array();

		// Check if all requirements are met before proceeding.
		$requirements = $this->check_requirements( $request );
		if ( true === $requirements ) {
			$result = $this->send_email();
		} else {
			$result = $requirements;
		}

		// Create and return a WP_REST_Response with the result.
		return new WP_REST_Response( $result );
	}

	/**
	 * Checks the requirements for processing a request,
	 * specifically targeting the email parameter.
	 *
	 * @since 4.8.0
	 *
	 * @param \WP_REST_Request $request The REST request object.
	 * @return bool|array{status: string, message: string} Returns true if the email parameter is set in the request, sets the customer_email property, and returns true. Otherwise, returns array.
	 */
	public function check_requirements( $request ) {

		/** @var string $token */
		$token  = $request->get_param( 'token' );
		$action = 'manage-subscriptions';
		// Check form token.
		// This is done here to avoid double increments (in authorization callback)
		// and more human-friendly error messages.
		if ( false === TokenValidationUtils::validate_token( $token, $action ) ) {
			return array(
				'status'  => 'error',
				'message' => __( 'Invalid CAPTCHA. Please try again.', 'stripe' ),
			);
		}

		if ( isset( $request->get_params()['email'] ) ) {
			$this->customer_email = sanitize_email( $request->get_params()['email'] );
			return true;
		}

		return array(
			'status'  => 'error',
			'message' => __( 'Something went wrong', 'stripe' ),
		);
	}

	/**
	 * Retrieves customer information based on the provided email address.
	 *
	 * @since 4.8.0
	 *
	 * @return array<\SimplePay\Vendor\Stripe\Customer>|null
	 */
	private function get_customers() {
		// Check if customer email is not set.
		if ( ! $this->customer_email ) {
			return null;
		}

		$customers = null;

		// Retrieve customer information using SimplePay API.
		try {

			$customers = Customers\all(
				array(
					'email' => $this->customer_email,
					'limit' => 10,
				),
				array(
					'api_key' => simpay_get_secret_key(),
				)
			);

			return $customers->data; // @phpstan-ignore-line.
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Retrieve and store subscriptions for the current customer.
	 *
	 * This method fetches subscriptions for each customer associated with the current instance.
	 * Subscriptions are retrieved using the Stripe API, and the results are stored in the
	 * 'subscriptions' property of the current instance.
	 *
	 * @since 4.8.0
	 *
	 * @param array<\SimplePay\Vendor\Stripe\Customer> $customers List of customers.
	 * @return array<\SimplePay\Vendor\Stripe\Subscription> List of subscriptions.
	 */
	private function get_subscriptions( $customers ) {

		$subscriptions = array();
		// Check if there are customers associated with the current instance.
		if ( is_array( $customers ) ) {
			// Loop through each customer and retrieve their subscriptions.
			foreach ( $customers as $customer ) {
				// Extract customer ID.
				$customer_id = $customer->id;

				// Request subscriptions from the Stripe API and store the results.
				try {
					$subscription_data = Subscriptions\all(
						array( 'customer' => $customer_id ),
						array(
							'api_key' => simpay_get_secret_key(),
						)
					);
					$subscriptions     = array_merge( $subscriptions, $subscription_data->data ); // @phpstan-ignore-line.
				} catch ( \Exception $e ) {
					return $subscriptions;
				}
			}
		}

		return $subscriptions;
	}



	/**
	 * Retrieves subscription links for each subscription and adds them to the object.
	 *
	 * @since 4.8.0
	 *
	 * @param array<\SimplePay\Vendor\Stripe\Subscription> $subscriptions list of subscriptions.
	 * @return array<int, array<string, string>> list of subscriptions link.
	 */
	public function get_subscription_links( $subscriptions ) {

		$subscription_links = array_map(
			function ( $subscription ) {
				if ( isset( $subscription->metadata, $subscription->metadata->simpay_form_id ) ) {
					$metadata         = $subscription->metadata;
					$form_id          = $metadata->simpay_form_id;
					$subscription_key = $metadata->simpay_subscription_key; // @phpstan-ignore-line.

					$form = simpay_get_form( $form_id );

					if ( $form && property_exists( $form, 'payment_success_page' ) ) {
						/** @var int $date_created */
						$date_created = get_date_from_gmt(
							gmdate( 'Y-m-d H:i:s', $subscription->created ),
							'U'
						);

						/** @var string $format */
						$format       = get_option( 'date_format' );
						$date_created = date_i18n( $format, $date_created );
						return array(
							'title' => $form->company_name,
							'url'   => esc_url_raw(
								add_query_arg(
									array(
										'customer_id'      => $subscription->customer,
										'subscription_key' => $subscription_key,
										'form_id'          => $form_id,
									),
									$form->payment_success_page
								)
							),
							'date'  => $date_created,
						);

					}
				}

				return null;
			},
			$subscriptions
		);

		// Filter out null values (subscriptions that didn't meet the conditions).
		$subscription_links = array_filter( $subscription_links );

		return $subscription_links;
	}



	/**
	 * Sends an email with subscription links.
	 *
	 * @since 4.8.0
	 *
	 * @return array{status: string, message: string} response message with status.
	 */
	public function send_email() {

		$customers = $this->get_customers();
		if ( ! $customers ) {
			return array(
				'status'  => 'error',
				'message' => __( 'No purchases were found for the supplied email address.', 'stripe' ),
			);
		}

		// Check if there are subscription links to send.
		$subscriptions = $this->get_subscriptions( $customers );

		if ( empty( $subscriptions ) ) {
			return array(
				'status'  => 'error',
				'message' => __( 'No purchases were found for the supplied email address.', 'stripe' ),
			);
		}

		/** @var \SimplePay\Core\Emails\Email\ManageSubscriptionsEmail $email */
		$email = $this->email;

		// Subscriptions links.
		$subscription_links = $this->get_subscription_links( $subscriptions );
		if ( empty( $subscription_links ) ) {
			return array(
				'status'  => 'error',
				'message' => __( 'No purchases were found for the supplied email address.', 'stripe' ),
			);
		}
		// Format the email content with subscription links.
		$message = $this->format_email_content( $subscription_links );
		$mailer  = new Mailer( $email );
		$mailer->set_to( $this->customer_email );
		$mailer->set_subject( $email->get_subject() );
		$mailer->set_body( $email->get_body( $message ) );
		$mailer->send();

		return array(
			'status'  => 'success',
			'message' => sprintf(
				// Translators: %s is a placeholder for the customer's email.
				__( 'Secure password-free links to manage your past purchases for %s have been sent to your inbox. ', 'stripe' ),
				$this->customer_email
			),
		);
	}

	/**
	 * Format subscription links into an HTML email content.
	 *
	 * @since 4.8.0
	 *
	 * @param array<int, array<string, string>> $links An array containing subscription links.
	 *                     Each entry is an associative array with the title as the key and the link as the value.
	 * @return string Formatted HTML content for the email.
	 */
	private function format_email_content( $links ) {

		// Initialize the HTML content with a basic structure.
		$content = '';

		foreach ( $links as $link ) {
			$content .= sprintf(
				'<p><strong>%1$s</strong> on %2$s - <a href="%3$s" target="_blank">Manage</a></p>',
				esc_html( $link['title'] ),
				esc_html( $link['date'] ),
				esc_url( $link['url'] )
			);
		}

		// Return the formatted content.
		return $content;
	}
}
