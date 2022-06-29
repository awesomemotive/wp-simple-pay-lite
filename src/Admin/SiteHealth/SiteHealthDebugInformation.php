<?php
/**
 * Site Health: Debug information
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.7
 */

namespace SimplePay\Core\Admin\SiteHealth;

use SimplePay\Core\API;
use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;
use SimplePay\Core\reCAPTCHA;
use SimplePay\Core\Utils;
use SimplePay\Pro\Webhooks\Database\Query as WebhookDatabase;

/**
 * SiteHealthDebugInformation class.
 *
 * @since 4.4.7
 */
class SiteHealthDebugInformation implements SubscriberInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Event manager.
	 *
	 * @since 4.4.7
	 * @var \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	private $events;

	/**
	 * SiteHealthDebugInformation.
	 *
	 * @since 4.4.7
	 *
	 * @param \SimplePay\Core\EventManagement\EventManager $events Event manager.
	 */
	public function __construct( $events ) {
		$this->events = $events;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'admin_init' => 'maybe_filter_debug_information',
		);
	}

	/**
	 * Filters the Site Health debug information.
	 *
	 * If the `simpay` query argument is in the URL, remove all other additions.
	 *
	 * @since 4.4.7
	 *
	 * @return void
	 */
	function maybe_filter_debug_information() {
		if ( isset( $_GET['simpay'] ) ) {
			// @todo EventManager does not support remove_all_*
			remove_all_filters( 'debug_information' );
		}

		$this->events->add_callback(
			'debug_information',
			array( $this, 'debug_information' )
		);
	}

	/**
	 * Returns the WPSP version
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function get_plugin_version() {
		return (string) SIMPLE_PAY_VERSION; // @phpstan-ignore-line
	}

	/**
	 * Returns "Yes" if TLS check is successful, otherwise "No"
	 *
	 * If keys have not been set, returns "Cannot test TLS 1.2 support until your Stripe Test Secret Key is entered."
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function stripe_tls_check() {
		$test_key = simpay_get_setting( 'test_secret_key', '' );
		$live_key = simpay_get_setting( 'live_secret_key', '' );

		// If test key isn't set.
		if ( empty( $test_key ) && empty( $live_key ) ) {
			return __( 'Cannot test TLS 1.2 support until your Stripe Test Secret Key is entered.', 'stripe' );
		}

		// Attempt to make an API request.
		try {
			API\Customers\all(
				array(
					'limit' => 1,
				),
				array(
					'api_key' => simpay_get_secret_key(),
				)
			);
			return __( 'Yes', 'stripe' );
		} catch ( \SimplePay\Vendor\Stripe\Exception\ApiConnectionException $e ) {
			return __( 'No', 'stripe' );
		}
	}

	/**
	 * Returns "Test Mode" or "Live Mode" depending on the mode set in WPSP Stripe settings
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function get_test_or_live_mode() {
		return simpay_is_test_mode()
			? __( 'Test Mode', 'stripe' )
			: __( 'Live Mode', 'stripe' );
	}

	/**
	 * Returns "Yes" if reCAPTCHA has been implemented, otherwise "No"
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function check_repatcha_keys() {
		return reCAPTCHA\has_keys()
			? __( 'Yes', 'stripe' )
			: __( 'No', 'stripe' );
	}

	/**
	 * Returns "Yes" if the rate limit file exists, otherwise "No"
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function check_rate_limiting_file() {
		$rate_limiting = new Utils\Rate_Limiting();
		$rate_limiting->setup_log_file();

		return $rate_limiting->has_file()
			? __( 'Yes', 'stripe' )
			: __( 'No', 'stripe' );
	}

	/**
	 * Returns the date created of the last event if receivng events, otherwise "none"
	 *
	 * Lite version of WPSP returns "Not reported in Lite Mode"
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function get_latest_webhook_event() {
		if ( $this->license->is_pro() ) {
			$livemode = ! simpay_is_test_mode();
			$webhooks = new WebhookDatabase();
			$webhooks = $webhooks->query(
				array(
					'number'   => 1,
					'livemode' => $livemode,
				)
			);

			$latest = __( 'None', 'stripe' );

			/** @var array<\SimplePay\Pro\Webhooks\Database\Row> $webhooks */
			if ( ! empty( $webhooks ) ) {
				/** @var string $latest */
				$latest = current( $webhooks )->date_created;
			}

			return $latest;
		} else {
			return 'Not reported in Lite Mode';
		}
	}

	/**
	 * Returns "Yes" if the webhook secret is added, otherwise "No"
	 *
	 * Lite version of WPSP returns "Not reported in Lite Mode"
	 *
	 * @since 4.4.7
	 *
	 * @return string
	 */
	private function get_webhook_secret() {
		if ( $this->license->is_pro() ) {
			$prefix = simpay_is_test_mode()
				? 'test'
				: 'live';

			$endpoint_secret = simpay_get_setting(
				$prefix . '_webhook_endpoint_secret',
				''
			);

			return ! empty( $endpoint_secret )
				? __( 'Yes', 'stripe' )
				: __( 'No', 'stripe' );
		} else {
			return 'Not reported in Lite Mode';
		}
	}

	/**
	 * Filters the debug information to include our plugin-specific site health
	 * panel in the generated UI.
	 *
	 * @since 4.4.7
	 *
	 * @param array<string, array<string, string>|string> $debug_info Site health debug information.
	 * @return array<string, array<string, array<string, array<string, string>>|string>|string>
	 */
	public function debug_information( $debug_info ) {
		$plugin = array(
			'label'  => 'WP Simple Pay',
			'fields' => array(
				'version'         => array(
					'label' => __( 'Version', 'stripe' ),
					'value' => $this->get_plugin_version(),
				),
				'stripetls'       => array(
					'label' => __( 'Stripe TLS', 'stripe' ),
					'value' => $this->stripe_tls_check(),
				),
				'mode'            => array(
					'label' => __( 'Global Payment Mode', 'stripe' ),
					'value' => $this->get_test_or_live_mode(),
				),
				'recaptcha'       => array(
					'label' => __( 'reCAPTCHA', 'stripe' ),
					'value' => $this->check_repatcha_keys(),
				),
				'rate_limit_file' => array(
					'label' => __( 'Rate Limit File', 'stripe' ),
					'value' => $this->check_rate_limiting_file(),
				),
				'recent_webhook'  => array(
					'label' => __( 'Most Recent Webhook Event', 'stripe' ),
					'value' => $this->get_latest_webhook_event(),
				),
				'webhook_secret'  => array(
					'label' => __( 'Webhook Secret', 'stripe' ),
					'value' => $this->get_webhook_secret(),
				),
			),
		);

		// Be respectful and keep ours at the bottom if showing all debug information.
		if ( ! isset( $_GET['simpay'] ) ) {
			return array_merge(
				$debug_info,
				array(
					'wp-simple-pay' => $plugin,
				)
			);
		}

		// Put ours at the top otherwise.
		return array_merge(
			array(
				'wp-simple-pay' => $plugin,
			),
			$debug_info
		);
	}

}
