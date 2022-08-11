<?php
/**
 * Payment Page: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 */

namespace SimplePay\Core\PaymentPage;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * PaymentPageServiceProvider class.
 *
 * @since 4.5.0
 */
class PaymentPageServiceProvider extends AbstractPluginServiceProvider {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'payment-page-output',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		$container->share(
			'payment-page-output',
			PaymentPageOutput::class
		)
			->withArgument( $container->get( 'event-manager' ) );
	}

}
