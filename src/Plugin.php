<?php
/**
 * Plugin
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core;

use SimplePay\Core\EventManagement\EventManager;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * Plugin class.
 *
 * @since 4.4.0
 */
final class Plugin {

	/**
	 * Plugin base filename with full path.
	 *
	 * @since 4.4.0
	 * @var string
	 */
	public $file;

	/**
	 * Plugin container.
	 *
	 * @since 4.4.0
	 * @var \SimplePay\Vendor\League\Container\Container
	 */
	protected $container;

	/**
	 * Plugin.
	 *
	 * @since 4.4.0
	 *
	 * @param string $file Plugin base filename with full path.
	 */
	public function __construct( $file ) {
		$this->file = $file;
	}

	/**
	 * Loads the plugin on the plugins_loaded hook.
	 *
	 * @since 4.4.0
	 *
	 * @return void
	 */
	public function load() {
		// Run slightly early to gain access to legacy registries.
		add_action( 'plugins_loaded', array( $this, 'register' ), 5 );
	}

	/**
	 * Sets up and returns the basic container instance.
	 *
	 * This is separate from the registration of service providers and subscribers
	 * to allow modifications to the container to be made before said items are executed.
	 *
	 * @since 4.4.0
	 *
	 * @return \SimplePay\Core\PluginContainer
	 */
	public function setup_container() {
		$this->container = new PluginContainer;

		// Event management.
		$this->container->share(
			'event-manager',
			EventManagement\EventManager::class
		);

		return $this->container;
	}

	/**
	 * Registers the plugin's service providers and subscribers.
	 *
	 * @since 4.4.0
	 *
	 * @return \SimplePay\Core\PluginContainer
	 */
	public function register() {
		if ( ! $this->container instanceof PluginContainer ) {
			$this->container = $this->setup_container();
		}

		// Register service providers.
		foreach ( $this->get_service_providers() as $service_provider ) {
			$this->container->addServiceProvider( $service_provider );
		}

		// Find event manager.
		$events = $this->container->get( 'event-manager' );

		if ( ! $events instanceof EventManager ) {
			return $this->container;
		}

		// Attach service provider subscribers to the event manager.
		foreach ( $this->get_service_providers() as $service_provider ) {
			if ( ! $service_provider instanceof AbstractPluginServiceProvider ) {
				continue;
			}

			/** @var \SimplePay\Core\AbstractPluginServiceProvider $service_provider */
			$subscribers = $service_provider->get_subscribers();

			foreach ( $subscribers as $subscriber_id ) {
				$subscriber = $this->container->get( $subscriber_id );

				if ( $subscriber instanceof SubscriberInterface ) {
					$events->add_subscriber( $subscriber );
				}
			}
		}

		return $this->container;
	}

	/**
	 * Retrieves service providers for the derived context.
	 *
	 * @since 4.4.0
	 *
	 * @return \SimplePay\Vendor\League\Container\ServiceProvider\ServiceProviderInterface[]
	 */
	private function get_service_providers() {
		$service_providers = array(
			new License\LicenseServiceProvider,
		);

		if ( is_admin() ) {
			$admin_service_providers = array(
				new Admin\Addon\AddonServiceProvider,
				new Admin\Education\EducationServiceProvider,
				new Admin\Page\AdminPageServiceProvider,
				new License\LicenseServiceProvider,
			);

			return array_merge( $admin_service_providers, $service_providers );
		}

		return $service_providers;
	}

}
