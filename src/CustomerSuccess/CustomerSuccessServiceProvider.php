<?php
/**
 * Customer success: First form embed
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\CustomerSuccess;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * CustomerSuccessServiceProvider class.
 *
 * @since 4.4.6
 */
class CustomerSuccessServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'customer-success-achievements',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'customer-success-achievement-first-form',
			'customer-success-achievement-first-form-embed',
			'customer-success-achievement-first-test-payment',
			'customer-success-achievement-go-live',
			'customer-success-achievement-first-live-payment',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Customer achievements.
		$container->share(
			'customer-success-achievements',
			CustomerAchievements::class
		);

		// Achievements.

		$achievements    = $container->get( 'customer-success-achievements' );
		/** @var bool|int $journey_started */
		$journey_started = get_option( 'simpay_customer_journey_start', false );

		if (
			$achievements instanceof CustomerAchievements &&
			// Only add achievements if the journey has started.
			false !== $journey_started
		) {
			// First form.
			$container->share(
				'customer-success-achievement-first-form',
				Achievement\FirstForm::class
			)
				->withArgument( $achievements );

			// First form embed.
			$container->share(
				'customer-success-achievement-first-form-embed',
				Achievement\FirstFormEmbed::class
			)
				->withArgument( $achievements );

			// First test payment.
			$container->share(
				'customer-success-achievement-first-test-payment',
				Achievement\FirstTestPayment::class
			)
				->withArgument( $achievements );

			// Go live.
			$container->share(
				'customer-success-achievement-go-live',
				Achievement\GoLive::class
			)
				->withArgument( $achievements );

			// First live payment.
			$container->share(
				'customer-success-achievement-first-live-payment',
				Achievement\FirstLivePayment::class
			)
				->withArgument( $achievements );
		}
	}

}
