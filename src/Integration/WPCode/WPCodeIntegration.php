<?php
/**
 * Divi: WPCode
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.11
 */

namespace SimplePay\Core\Integration\WPCode;

use SimplePay\Core\Integration\AbstractIntegration;

/**
 * WPCode class.
 *
 * @since 4.7.11
 */
class WPCodeIntegration extends AbstractIntegration {

	/**
	 * {@inheritdoc}
	 */
	public function get_id() {
		return 'wpcode';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_active() {
		return function_exists( 'wpcode_register_library_username' );
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
			'integration-wpcode-library',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Extension subscriber.
		$container->share(
			'integration-wpcode-library',
			WPCodeLibrarySubscriber::class
		);
	}

}
