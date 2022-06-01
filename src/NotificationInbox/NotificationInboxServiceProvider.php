<?php
/**
 * Notification inbox: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

use SimplePay\Core\AbstractPluginServiceProvider;
use SimplePay\Vendor\League\Container\ServiceProvider\BootableServiceProviderInterface;

/**
 * NotificationInboxServiceProvider class.
 *
 * @since 4.4.5
 */
class NotificationInboxServiceProvider extends AbstractPluginServiceProvider implements BootableServiceProviderInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'notification-inbox-rule-processor',
			'notification-inbox-repository',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		$subscribers = array(
			'notification-inbox-remote-importer',
		);

		if ( is_admin() ) {
			$subscribers[] = 'notification-inbox-ui';
		}

		return $subscribers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		$container = $this->getContainer();

		// Install repository table.
		// Create the table with BerlinDB.
		// Call maybe_upgrade() immediately instead of waiting for admin_init.
		$table = new Database\Table;
		$table->maybe_upgrade();

		// Rule processor.
		$container->share(
			'notification-inbox-rule-processor',
			NotificationRuleProcessor::class
		);

		// Repository.
		$container->share(
			'notification-inbox-repository',
			NotificationRepository::class
		)
			->withArgument( $container->get( 'notification-inbox-rule-processor' ) );

		$inflector = $container->inflector( NotificationAwareInterface::class );

		if ( $inflector instanceof \SimplePay\Vendor\League\Container\Inflector\Inflector ) {
			$inflector->invokeMethod(
				'set_notifications',
				array( $this->container->get( 'notification-inbox-repository' ) )
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Importer.
		$container->share(
			'notification-inbox-remote-importer',
			NotificationImporter\RemoteNotificationImporter::class
		)
			->withArgument( $this->get_remote_api_url() )
			->withArgument( $container->get( 'scheduler' ) )
			->withArgument( $container->get( 'notification-inbox-repository' ) )
			->withArgument( $container->get( 'notification-inbox-rule-processor' ) );

		// UI.
		$container->share(
			'notification-inbox-ui',
			NotificationInboxUi::class
		);
	}

	/**
	 * Returns the API URL for remote notifications to import.
	 *
	 * @since 4.4.5
	 *
	 * @return string
	 */
	private function get_remote_api_url() {
		if ( defined( 'SIMPAY_NOTIFICATION_INBOX_API_URL' ) ) {
			return SIMPAY_NOTIFICATION_INBOX_API_URL;
		}

		return 'https://plugin.wpsimplepay.com/wp-content/notifications.json';
	}

}
