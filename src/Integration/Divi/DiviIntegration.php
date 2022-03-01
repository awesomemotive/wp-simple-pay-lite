<?php
/**
 * Divi: Integration
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Integration\Divi;

use SimplePay\Core\Integration\AbstractIntegration;

/**
 * DiviIntegration class.
 *
 * @since 4.4.3
 */
class DiviIntegration extends AbstractIntegration {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'divi';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active() {
		return 'Divi' === get_template() || defined( 'ET_BUILDER_PLUGIN_VERSION' );
	}

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
			'integration-divi-subscriber',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Extension subscriber.
		$container->share(
			'integration-divi-subscriber',
			ExtensionSubscriber::class
		)
			->withArgument( $container->get( 'event-manager' ) );
	}

}
