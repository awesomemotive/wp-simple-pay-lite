<?php
/**
 * Elementor: Integration
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Elementor;

use SimplePay\Core\Integration\AbstractIntegration;

/**
 * ElementorIntegration class.
 *
 * @since 4.4.3
 */
class ElementorIntegration extends AbstractIntegration {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'elementor';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active() {
		return defined( 'ELEMENTOR_VERSION' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'integration-elementor-payment-form-control',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'integration-elementor-button-widget',
			'integration-elementor-price-table-widget',
			'integration-elementor-call-to-action-widget',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Shared control.
		$container->share(
			'integration-elementor-payment-form-control',
			PaymentFormControl::class
		);

		$control = $container->get(
			'integration-elementor-payment-form-control'
		);

		// "Button" widget.
		$container->share(
			'integration-elementor-button-widget',
			ButtonWidget::class
		)
			->withArgument( $control );

		// "Price Table" widget.
		$container->share(
			'integration-elementor-price-table-widget',
			PriceTableWidget::class
		)
			->withArgument( $control );

		// "Call to Action" widget.
		$container->share(
			'integration-elementor-call-to-action-widget',
			CallToActionWidget::class
		)
			->withArgument( $control );
	}

}
