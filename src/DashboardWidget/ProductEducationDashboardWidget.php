<?php
/**
 * Dashboard widget: Product education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\DashboardWidget;

use SimplePay\Core\Admin\Notice_Manager;
use SimplePay\Core\License\License;
use SimplePay\Core\Settings;

/**
 * ProductEducationDashboardWidget class.
 *
 * @since 4.4.0
 */
class ProductEducationDashboardWidget extends AbstractDashboardWidget {

	/**
	 * Minimum WordPress version required to show the report.
	 *
	 * @since 4.4.6
	 */
	const REPORT_MIN_WP_VERSION = '5.7';

	/**
	 * License.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Core\License\License
	 */
	private $license;

	/**
	 * ProductEducationDashboardWidget.
	 *
	 * @since 4.4.0
	 *
	 * @param \SimplePay\Core\License\License $license Plugin license.
	 */
	public function __construct( License $license ) {
		$this->license = $license;
	}

	/**
	 * {@inheritdoc}
	 */
	public function can_register() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		global $wp_version;

		// If the minimum WordPress for the report is met there will always be something to show.
		if ( version_compare( $wp_version, self::REPORT_MIN_WP_VERSION, '>=' ) ) {
			return true;
		}

		// If the minimum WordPress version for the report is not met, check if
		// there is still something relevant to display.
		return (
			true === $this->should_display_stripe_connect() ||
			true === $this->should_display_first_form() ||
			true === $this->should_display_lite_upgrade()
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'simpay-product-education';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		return 'WP Simple Pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		return array();
	}

	/**
	 * Determines if the widget should display the Stripe Connect view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_stripe_connect() {
		return empty( simpay_get_secret_key() );
	}

	/**
	 * Determines if the widget should display the first form view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_first_form() {
		$forms = array_map( 'intval', (array) wp_count_posts( 'simple-pay' ) );

		return 0 === $forms['publish'];
	}

	/**
	 * Determines if the widget should display the Lite upgrade view.
	 *
	 * @since 4.4.0
	 *
	 * @return bool
	 */
	public function should_display_lite_upgrade() {
		return $this->license->is_lite();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @todo use a ViewLoader
	 */
	public function render() {
		// No Stripe connection.
		if ( true === $this->should_display_stripe_connect() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-stripe-connect.php'; // @phpstan-ignore-line

			return;
		}

		// No payment forms.
		if ( true === $this->should_display_first_form() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-first-form.php'; // @phpstan-ignore-line

			return;
		}

		global $wp_version;

		// Show report if WordPress minimum version is met.
		if ( version_compare( $wp_version, self::REPORT_MIN_WP_VERSION, '>=' ) ) {
			$this->get_report_view();
			return;

			// .. or show a lite Lite upgrade if on lite.
		} else if ( true === $this->should_display_lite_upgrade() ) {
			include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-lite-upgrade.php'; // @phpstan-ignore-line

			return;
		}
	}

	/**
	 * Returns the dashboard widget report view.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	private function get_report_view() {
		$asset = SIMPLE_PAY_INC . 'core/assets/js/simpay-admin-dashboard-widget-report.min.asset.php'; // @phpstan-ignore-line

		if ( ! file_exists( $asset ) ) {
			return;
		}

		$asset_data = include_once $asset;

		wp_enqueue_script(
			'simpay-admin-dashboard-widget-report',
			SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin-dashboard-widget-report.min.js', // @phpstan-ignore-line
			array_merge(
				array(
					'simpay-accounting',
				),
				$asset_data['dependencies']
			),
			$asset_data['version'],
			true
		);

		$default_date_range = get_user_meta(
			get_current_user_id(),
			'simpay_dashboard_widget_report_date_range',
			true
		);

		if ( empty( $default_date_range ) ) {
			$default_date_range = 'last7';
		}

		$user_id = get_current_user_id();

		/** @var string $default_currency */
		$default_currency = get_user_meta(
			$user_id,
			'simpay_dashboard_widget_report_currency',
			true
		);

		if ( empty( $default_currency ) ) {
			/** @var string $default_currency */
			$default_currency = simpay_get_setting( 'currency', 'USD' );
		}

		wp_localize_script(
			'simpay-admin-dashboard-widget-report',
			'simpayAdminDashboardWidgetReport',
			array(
				'i18n'               => array(),
				'user_id'            => $user_id,
				'license'            => $this->license->to_array(),
				'currencies'         => array_keys( simpay_get_currencies() ),
				'default_currency'   => strtolower( $default_currency ),
				'default_date_range' => $default_date_range,
				'webhooks_url'       => Settings\get_url(
					array(
						'section'    => 'stripe',
						'subsection' => 'webhooks',
					)
				),
			)
		);

		wp_set_script_translations(
			'simpay-admin-dashboard-widget-report',
			'simple-pay',
			SIMPLE_PAY_DIR . '/languages' // @phpstan-ignore-line
		);

		wp_enqueue_style(
			'simpay-admin-dashboard-widget-report',
			SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-dashboard-widget-report.min.css', // @phpstan-ignore-line
			array(
				'wp-components',
			),
			$asset_data['version']
		);

		// Recommended plugin.
		$recommended_plugin = $this->get_recommended_plugin();

		$dismissed_recommended_plugin = (bool) Notice_Manager::is_notice_dismissed(
			'simpay-dashboard-widget-recommended-plugin'
		);

		// Load view.
		include_once SIMPLE_PAY_DIR . 'views/admin-dashboard-widget-report.php'; // @phpstan-ignore-line return;
	}

	/**
	 * Returns one of the recommended plugins to cross-promote in the dashboard widget.
	 * Filters out items that have already been installed.
	 *
	 * @since 4.4.6
	 *
	 * @return array<string, array<string, string>|string>
	 */
	private function get_recommended_plugin() {
		$plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => array(
				'name' => __( 'MonsterInsights', 'stripe' ),
				'slug' => 'google-analytics-for-wordpress',
				'more' => 'https://www.monsterinsights.com/',
				'pro'  => array(
					'file' => 'google-analytics-premium/googleanalytics-premium.php',
				),
			),
			'all-in-one-seo-pack/all_in_one_seo_pack.php'       => array(
				'name' => __( 'AIOSEO', 'stripe' ),
				'slug' => 'all-in-one-seo-pack',
				'more' => 'https://aioseo.com/',
				'pro'  => array(
					'file' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
				),
			),
			'coming-soon/coming-soon.php'                       => array(
				'name' => __( 'SeedProd', 'stripe' ),
				'slug' => 'coming-soon',
				'more' => 'https://www.seedprod.com/',
				'pro'  => array(
					'file' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
				),
			),
			'wp-mail-smtp/wp_mail_smtp.php'                     => array(
				'name' => __( 'WP Mail SMTP', 'stripe' ),
				'slug' => 'wp-mail-smtp',
				'more' => 'https://wpmailsmtp.com/',
				'pro'  => array(
					'file' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
				),
			),
		);

		$installed = get_plugins();

		// Remove installed plugins from choice list.
		$plugins = array_filter(
			$plugins,
			function( $plugin, $id ) use ( $installed ) {
				return (
					! isset( $installed[ $id ] ) &&
					! isset( $installed[ $plugin['pro']['file'] ] )
				);
			},
			ARRAY_FILTER_USE_BOTH
		);

		// Add an install URL to each.
		$plugins = array_map(
			function( $plugin ) {
				$plugin['install_url'] = wp_nonce_url(
					add_query_arg(
						array(
							'action' => 'install-plugin',
							'plugin' => rawurlencode( $plugin['slug'] )
						),
						self_admin_url( 'update.php' )
					),
					'install-plugin_' . $plugin['slug']
				);

				return $plugin;
			},
			$plugins
		);

		return ! empty( $plugins )
			? $plugins[ array_rand( $plugins ) ]
			: array();
	}

}
