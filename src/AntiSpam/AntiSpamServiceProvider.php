<?php
/**
 * Anti-Spam: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.0
 */

namespace SimplePay\Core\AntiSpam;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * AntiSpamServiceProvider class.
 *
 * @since 4.6.0
 */
class AntiSpamServiceProvider extends AbstractPluginServiceProvider {

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
			'anti-spam-email-verification',
			'anti-spam-require-authentication',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Eamil verification.
		$container->share(
			'anti-spam-email-verification',
			EmailVerification::class
		)
			->withArgument( $container->get( 'scheduler' ) );

		// Require authentication.
		$container->share(
			'anti-spam-require-authentication',
			RequireAuthentication::class
		);
	}

}
