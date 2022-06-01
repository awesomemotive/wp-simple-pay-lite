<?php
/**
 * Customer Success: First test payment
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
class FirstTestPayment implements SubscriberInterface {

	const ACHIEVEMENT = 'first-test-payment';

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
			'simpay_before_customer_from_payment_form_request'         =>
				array( 'first_onsite_test_payment', 10, 2 ),
			'simpay_before_checkout_session_from_payment_form_request' =>
				array( 'first_checkout_test_payment', 10, 2 ),
		);
	}

	/**
	 * Adds the first test form payment achievement via on-site payment forms.
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed>                   $args Customer arguments.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return void
	 */
	public function first_onsite_test_payment( $args, $form ) {
		// Do not run in livemode.
		if ( true === $form->is_livemode() ) {
			return;
		}

		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

	/**
	 * Adds the first test form payment achievement via Stripe Checkout payment forms.
	 *
	 * @since 4.4.6
	 *
	 * @param array<mixed>                   $args Checkout arguments.
	 * @param \SimplePay\Core\Abstracts\Form $form Payment form.
	 * @return void
	 */
	public function first_checkout_test_payment( $args, $form ) {
		// Do not run in livemode.
		if ( true === $form->is_livemode() ) {
			return;
		}

		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

}
