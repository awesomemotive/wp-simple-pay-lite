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
			'site_health_navigation_tabs' => 'maybe_remove_auto_update_string',
			'admin_init'                  => 'maybe_filter_debug_information',
		);
	}

	/**
	 * Potentially adds a filter to the "Auto-Update Debug" string.
	 *
	 * Utilizing the filter `site_health_navigation_tabs` because it runs before
	 * the page is output. Not available in WP < 5.8.
	 *
	 * @since 4.6.4
	 *
	 * @param array<string, string> $tabs Site Health tabs.
	 * @return array<string, string>
	 */
	function maybe_remove_auto_update_string( $tabs ) {
		add_filter( 'plugin_auto_update_debug_string', '__return_empty_string' );

		return $tabs;
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
	 * Returns the Stripe Account ID, or - if not using Connect.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_stripe_account_id() {
		$account_id = simpay_get_account_id();

		return ! empty( $account_id ) ? $account_id : '-';
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
	 * Returns "Yes" or "No" depending on if UPE is being used
	 *
	 * @since 4.7.0
	 *
	 * @return string
	 */
	private function get_upe_yes_or_upe_no() {
		return simpay_is_upe()
			? __( 'Yes', 'stripe' )
			: __( 'No', 'stripe' );
	}

	/**
	 * Returns "Enabled" or "Disabled" depending on Opinonated styles setting
	 *
	 * @since 4.7.6.1
	 *
	 * @return string
	 */
	private function get_opinionated_styles_enabled() {
		$default_plugin_styles = simpay_get_setting( 'default_plugin_styles', 'enabled' );

		return 'enabled' === $default_plugin_styles
			? __( 'Enabled', 'stripe' )
			: __( 'Disabled', 'stripe' );
	}

	/**
	 * Returns the CAPTCHA type.
	 *
	 * @since 4.6.6
	 *
	 * @return string
	 */
	private function check_captcha() {
		$existing_recaptcha = simpay_get_setting( 'recaptcha_site_key', '' );
		$default            = ! empty( $existing_recaptcha )
			? 'recaptcha-v3'
			: '';
		$type               = simpay_get_setting( 'captcha_type', $default );

		switch ( $type ) {
			case 'recaptcha-v3':
				return 'reCAPTCHA v3';
			case 'hcaptcha':
				return 'hCaptcha';
			default:
				return 'None';
		}
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
	 * Returns anit-spam email verification status.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	private function get_email_verification() {
		/** @var string $enabled */
		$enabled = simpay_get_setting(
			'fraud_email_verification',
			'yes'
		);

		if ( 'no' === $enabled ) {
			return 'No';
		}

		/** @var int $threshold */
		$threshold = simpay_get_setting(
			'fraud_email_verification_threshold',
			3
		);

		/** @var int|float $timeframe */
		$timeframe = simpay_get_setting(
			'fraud_email_verification_timeframe',
			6
		);

		return sprintf(
			'%s: %d events in %d hours',
			ucfirst( $enabled ),
			$threshold,
			$timeframe
		);
	}

	/**
	 * Returns the anti-spam require authentication.
	 *
	 * @since 4.6.0
	 *
	 * @return string
	 */
	private function get_require_authentication() {
		/** @var string $enabled */
		$enabled = simpay_get_setting(
			'fraud_require_authentication',
			'no'
		);

		return ucfirst( $enabled );
	}

	/**
	 * Returns a list of plugin slugs that may conflict with WP Simple Pay.
	 *
	 * @since 4.6.4
	 *
	 * @return array<string>
	 */
	private function get_potential_plugin_conflict_blocklist() {
		return array(
			'autoptimize/autoptimize.php',
			'cleantalk-spam-protect/cleantalk.php',
			'defender-security/wp-defender.php',
			'schema-app-structured-data-for-schemaorg/hunch-schema.php',
			'sg-cachepress/sg-cachepress.php',
			'wordfence/wordfence.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-optimize/wp-optimize.php',
			'wp-rocket/wp-rocket.php',
			'wp-simple-firewall/icwp-wpsf.php',
			'wp-super-cache/wp-cache.php',
			'litespeed-cache/litespeed-cache.php',
		);
	}

	/**
	 * Returns a list of active plugins.
	 *
	 * @since 4.6.4
	 *
	 * @return array<string, array<string, string>>
	 */
	private function get_active_plugin_list() {
		$plugins = get_plugins();
		$plugins = array_filter(
			$plugins,
			function( $plugin_path ) {
				return is_plugin_active( $plugin_path );
			},
			ARRAY_FILTER_USE_KEY
		);

		return $plugins;
	}

	/**
	 * Returns a list of plugin names that may be conflicting with WP Simple Pay.
	 *
	 * @since 4.6.4
	 *
	 * @return string List of plugin names that may cause conflict. Blank if there are no conflicts.
	 */
	private function get_potential_plugin_conflicts() {
		$active_plugins      = $this->get_active_plugin_list();
		$potential_conflicts = $this->get_potential_plugin_conflict_blocklist();

		$plugins = array_filter(
			$active_plugins,
			function( $plugin_path ) use ( $potential_conflicts ) {
				return in_array(
					$plugin_path,
					$potential_conflicts,
					true
				);
			},
			ARRAY_FILTER_USE_KEY
		);

		if ( empty( $plugins ) ) {
			return '';
		}

		$plugins = array_map(
			function( $plugin_data ) {
				return $plugin_data['Name'];
			},
			$plugins
		);

		$plugin_names = array_values( $plugins );
		sort( $plugin_names );

		$plugin_names = implode( ', ', $plugin_names );

		return $plugin_names;
	}

	/**
	 * Filters the debug information to include our plugin-specific site health
	 * panel in the generated UI.
	 *
	 * @since 4.4.7
	 *
	 * @param array<string, array<string, array<string, array<string, string>|string>|string>> $debug_info Site health debug information.
	 * @return array<string, array<string, array<string, array<string, string>|string>|string>>
	 */
	public function debug_information( $debug_info ) {
		$plugin = array(
			'label'  => 'WP Simple Pay',
			'fields' => array(
				'version'                  => array(
					'label' => __( 'Version', 'stripe' ),
					'value' => $this->get_plugin_version(),
				),
				'stripetls'                => array(
					'label' => __( 'Stripe TLS', 'stripe' ),
					'value' => $this->stripe_tls_check(),
				),
				'stripe_account_id'        => array(
					'label' => __( 'Stripe Account ID', 'stripe' ),
					'value' => $this->get_stripe_account_id(),
				),
				'mode'                     => array(
					'label' => __( 'Global Payment Mode', 'stripe' ),
					'value' => $this->get_test_or_live_mode(),
				),
				'captcha'                  => array(
					'label' => __( 'CAPTCHA', 'stripe' ),
					'value' => $this->check_captcha(),
				),
				'rate_limit_file'          => array(
					'label' => __( 'Rate Limit File', 'stripe' ),
					'value' => $this->check_rate_limiting_file(),
				),
				'fraud_email_verification' => array(
					'label' => __( 'Email Verification', 'stripe' ),
					'value' => $this->get_email_verification(),
				),
				'fraud_require_auth'       => array(
					'label' => __( 'Require Authentication', 'stripe' ),
					'value' => $this->get_require_authentication(),
				),
				'recent_webhook'           => array(
					'label' => __( 'Most Recent Webhook Event', 'stripe' ),
					'value' => $this->get_latest_webhook_event(),
				),
				'webhook_secret'           => array(
					'label' => __( 'Webhook Secret', 'stripe' ),
					'value' => $this->get_webhook_secret(),
				),
				'opinionated_styles'       => array(
					'label' => __( 'Opinionated Styles', 'stripe' ),
					'value' => $this->get_opinionated_styles_enabled(),
				),
				'db_tables'                => array(
					'label' => __( 'Database Tables', 'stripe' ),
					'value' => $this->get_custom_database_tables(),
				),
				'upe'                      => array(
					'label' => __( 'Using UPE', 'stripe' ),
					'value' => $this->get_upe_yes_or_upe_no(),
				),
			),
		);

		$potential_conflicts = $this->get_potential_plugin_conflicts();

		if ( ! empty( $potential_conflicts ) ) {
			$plugin['fields']['potential_conflicts'] = array(
				'label' => __( '⚠️ Potential Conflicts', 'stripe' ),
				'value' => $potential_conflicts,
			);
		}

		// Be respectful and keep ours at the bottom if showing all debug information.
		if ( ! isset( $_GET['simpay'] ) ) {
			return array_merge(
				$debug_info,
				array(
					'wp-simple-pay' => $plugin,
				)
			);
		}

		unset( $debug_info['wp-themes-inactive'] );
		unset( $debug_info['wp-plugins-inactive'] );
		unset( $debug_info['wp-media'] );
		unset( $debug_info['wp-active-theme']['fields']['theme_features'] );

		/** @var array<string, array<string, string>> $active_plugins */
		$active_plugins = $debug_info['wp-plugins-active']['fields'];

		// Remove " | " after the auto update string has been filtered out. Pointless...
		$active_plugins_cleaned = array_map(
			function( $plugin_data ) {
				return array_merge(
					$plugin_data,
					array(
						'value' => str_replace( ' | ', '', $plugin_data['value'] ),
					)
				);
			},
			$active_plugins
		);

		$debug_info['wp-plugins-active']['fields'] = $active_plugins_cleaned;

		// Put ours at the top otherwise.
		return array_merge(
			array(
				'wp-simple-pay' => $plugin,
			),
			$debug_info
		);
	}

	/**
	 * Returns the custom database tables, and their versions.
	 *
	 * @since 4.7.10
	 *
	 * @return string
	 */
	private function get_custom_database_tables() {
		global $wpdb;

		$tables = array(
			'wpsp_coupons',
			'wpsp_notifications',
			'wpsp_transactions',
			'wpsp_webhooks',
		);

		$ret = '';

		foreach ( $tables as $table ) {
			$table_name = $wpdb->prefix . $table;

			$found = $wpdb->get_var(
				$wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name )
			);

			if ( empty( $found ) ) {
				$ret .= '⚠️ ' . $table . ' table not found';
			}
		}

		if ( '' === $ret ) {
			$ret = 'All tables found';
		}

		return $ret;
	}

}
