<?php
/**
 * Customer Success: Go live
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\CustomerSuccess\Achievement;

use SimplePay\Core\CustomerSuccess\CustomerAchievements;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * FirstTestForm class.
 *
 * @since 4.4.6
 */
class GoLive implements SubscriberInterface {

	const ACHIEVEMENT = 'go-live';

	/**
	 * Customer achievements.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\CustomerSuccess\CustomerAchievements
	 */
	private $achievements;

	/**
	 * FirstFormEmbedAchievement.
	 *
	 * @since 4.4.6
	 */
	public function __construct( CustomerAchievements $achievements ) {
		$this->achievements = $achievements;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'simpay_stripe_account_connected' => 'go_live',
		);
	}

	/**
	 * Adds the "go live" achievement when the global livemode setting changes.
	 *
	 * @since 4.4.6
	 *
	 * @return void
	 */
	public function go_live() {
		// Still in test mode.
		if ( true === simpay_is_test_mode() ) {
			return;
		}

		// Live keys do not exist (not reconnected).
		if ( empty( simpay_get_secret_key() ) ) {
			return;
		}

		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

}
