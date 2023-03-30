<?php
/**
 * Emails: Summary report
 *
 * @package SimplePay
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.3
 */

namespace SimplePay\Core\Emails\Email;

use DateTimeImmutable;
use SimplePay\Core\Report;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SummaryReportEmail class.
 *
 * @since 4.7.3
 */
class SummaryReportEmail extends AbstractEmail {

	use Report\ReportTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'summary-report';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type() {
		return AbstractEmail::INTERNAL_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Summary Report', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_description() {
		return __(
			'Receive a summary report of your website\'s payments, activity, and performance.',
			'stripe'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_licenses() {
		return array(
			'lite',
			'personal',
			'plus',
			'professional',
			'ultimate',
			'elite',
		);
	}

	/**
	 * Returns the intervals available to send the summary report.
	 *
	 * @since 4.7.0
	 *
	 * @return array<string, string>
	 */
	public function get_intervals() {
		return array(
			'weekly'  => __( 'Weekly', 'stripe' ),
			'monthly' => __( 'Monthly', 'stripe' ),
		);
	}

	/**
	 * Returns the interval to send the summary report. Set by the settings UI.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_send_interval() {
		/** @var string $interval */
		$interval = simpay_get_setting(
			sprintf( 'email_%s_interval', $this->get_id() ),
			'weekly'
		);

		return $interval;
	}

	/**
	 * Returns the email address(es) to send the email to.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_to() {
		/** @var string $to */
		$to = simpay_get_setting(
			sprintf( 'email_%s_to', self::get_id() ),
			get_bloginfo( 'admin_email' )
		);

		return $to;
	}

	/**
	 * Returns the subject of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_subject() {
		$intervals = $this->get_intervals();
		$interval  = isset( $intervals[ $this->get_send_interval() ] )
			? $intervals[ $this->get_send_interval() ]
			: $intervals['weekly'];

		return sprintf(
			/* translators: %1$s Send interval, %2$s Site domain, do not translate. */
			__( 'Your %1$s WP Simple Pay Summary for %2$s', 'stripe' ),
			$interval,
			$this->get_site_domain()
		);
	}

	/**
	 * Returns the body of the email.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	public function get_body() {
		$interval = $this->get_send_interval();

		switch ( $interval ) {
			case 'monthly':
				$modification = '-1 month';
				break;
			default:
				$modification = '-1 week';
		}

		$today = new DateTimeImmutable( 'now' );
		$range = new Report\DateRange(
			'custom',
			$today->modify( $modification )->format( 'Y-m-d 00:00:00' ),
			$today->format( 'Y-m-d 23:59:59' )
		);

		/** @var string $currency */
		$currency = simpay_get_setting( 'currency', 'USD' );

		$report = new Report\ActivityOverviewReport(
			$range,
			strtolower( $currency )
		);

		$stats       = $report->get_stats();
		$top_forms   = $report->get_top_forms();
		$site_domain = $this->get_site_domain();

		/** @var string $date_format */
		$date_format = get_option( 'date_format', 'F j, Y' );
		$start       = $range->start->format( $date_format );
		$end         = $range->end->format( $date_format );

		ob_start();

		require_once SIMPLE_PAY_DIR . 'views/email-internal-summary-report.php'; // @phpstan-ignore-line

		$html = ob_get_clean();

		return false === $html ? '' : $html;
	}

	/**
	 * Returns the site domain.
	 *
	 * @since 4.7.3
	 *
	 * @return string
	 */
	private function get_site_domain() {
		$parsed_home_url = wp_parse_url( home_url() );

		if ( false === $parsed_home_url ) {
			return home_url();
		}

		/** @var array<string, string> $parsed_home_url */

		$site_domain = $parsed_home_url['host'];

		if ( is_multisite() && isset( $parsed_home_url['path'] ) ) {
			$site_domain .= $parsed_home_url['path'];
		}

		$site_domain = set_url_scheme(
			$site_domain,
			is_ssl() ? 'https' : 'http'
		);

		return esc_url( $site_domain );
	}

}
