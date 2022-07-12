<?php
/**
 * Block: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Block;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * BlockServiceProvider class.
 *
 * @since 4.4.2
 */
class BlockServiceProvider extends AbstractPluginServiceProvider {

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
			'block-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		$container->share(
			'block-subscriber',
			BlockSubscriber::class
		)
			->withArgument( $this->get_blocks() );
	}

	/**
	 * Returns a list of block types to register.
	 *
	 * @since 4.4.2
	 *
	 * @return array<\SimplePay\Core\Block\BlockInterface>
	 */
	private function get_blocks() {
		$container = $this->getContainer();

		// Payment form.
		$container->share( 'block-payment-form', PaymentFormBlock::class );

		// Button.
		$container->share( 'block-button', ButtonBlock::class )
			->withArgument( $container->get( 'event-manager' ) );

		/** @var array<\SimplePay\Core\Block\BlockInterface> $blocks */
		$blocks = array(
			$container->get( 'block-payment-form' ),
			$container->get( 'block-button' ),
		);

		return $blocks;
	}

}
