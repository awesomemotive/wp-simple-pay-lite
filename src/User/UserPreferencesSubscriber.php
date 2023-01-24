<?php
/**
 * User: Preferences
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\User;

use SimplePay\Core\EventManagement\SubscriberInterface;
use SimplePay\Core\Report;

/**
 * UserPrefences class.
 *
 * @since 4.6.7
 */
class UserPreferencesSubscriber implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'init' => 'register_user_preferences',
		);
	}

	/**
	 * Registers user meta to allow saving the selected filter values.
	 *
	 * @since 4.6.7
	 *
	 * @return void
	 */
	public function register_user_preferences() {
		// Dashboard Widget: Date range.
		register_meta(
			'user',
			'simpay_dashboard_widget_report_range',
			Report\SchemaUtils::get_date_range_user_preferences_args() // @phpstan-ignore-line
		);

		// Dashboard Widget: Currency.
		register_meta(
			'user',
			'simpay_dashboard_widget_report_currency',
			Report\SchemaUtils::get_currency_user_preferences_args() // @phpstan-ignore-line
		);

		// Activity & Reporrts: Date range.
		register_meta(
			'user',
			'simpay_activity_reports_range',
			Report\SchemaUtils::get_date_range_user_preferences_args() // @phpstan-ignore-line
		);

		// Activity & Reports: Currency.
		register_meta(
			'user',
			'simpay_activity_reports_currency',
			Report\SchemaUtils::get_currency_user_preferences_args() // @phpstan-ignore-line
		);
	}

}
