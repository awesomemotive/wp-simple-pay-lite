<?php
/**
 * Plugin
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core;

use Exception;
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
		// Load Action Scheduler before plugins loaded.
		// https://actionscheduler.org/usage/#loading-action-scheduler
		if (
			! defined( 'SIMPAY_SCHEDULER_WP_CRON' ) ||
			false === SIMPAY_SCHEDULER_WP_CRON
		) {
			require_once dirname( $this->file ) . '/lib/woocommerce/action-scheduler/action-scheduler.php';
		}

		// Run slightly early to gain access to legacy registries.
		add_action( 'plugins_loaded', array( $this, 'register' ), 5 ); // @phpstan-ignore-line
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

		// Scheduler.
		$this->container->share(
			'scheduler',
			function() {
				$events = $this->container->get( 'event-manager' );

				if ( ! $events instanceof EventManager ) {
					return;
				}

				// Allow fallback to default WordPress cron.
				// @todo abstract as a more robust factory?
				if (
					defined( 'SIMPAY_SCHEDULER_WP_CRON' ) &&
					true === SIMPAY_SCHEDULER_WP_CRON
				) {
					return new Scheduler\WpCronScheduler( $events );
				}

				return new Scheduler\ActionScheduler;
			}
		);

		return $this->container;
	}

	/**
	 * Registers the plugin's service providers and subscribers.
	 *
	 * @since 4.4.0
	 *
	 * @return \SimplePay\Vendor\League\Container\Container
	 */
	public function register() {
		if ( ! $this->container instanceof PluginContainer ) {
			$this->container = $this->setup_container();
		}

		// Attach service provider subscribers to the event manager.
		$this->add_service_providers( $this->get_service_providers() );

		return $this->container;
	}

	/**
	 * Registers service providers (and their child subscribers) with the container.
	 *
	 * @since 4.4.3
	 *
	 * @param array<\SimplePay\Vendor\League\Container\ServiceProvider\ServiceProviderInterface> $service_providers
	 *                                                                                           Service providers.
	 * @return void
	 */
	private function add_service_providers( $service_providers ) {
		$events = $this->container->get( 'event-manager' );

		if ( ! $events instanceof EventManager ) {
			return;
		}

		// Register service providers.
		foreach ( $service_providers as $service_provider ) {
			if ( ! $service_provider instanceof AbstractPluginServiceProvider ) {
				continue;
			}

			$this->container->addServiceProvider( $service_provider );
		}

		// Attach subscribers.
		foreach ( $service_providers as $service_provider ) {
			if ( ! $service_provider instanceof AbstractPluginServiceProvider ) {
				continue;
			}

			/** @var \SimplePay\Core\AbstractPluginServiceProvider $service_provider */
			$subscribers = $service_provider->get_subscribers();

			foreach ( $subscribers as $subscriber_id ) {
				try {
					$subscriber = $this->container->get( $subscriber_id );

					if ( $subscriber instanceof SubscriberInterface ) {
						$events->add_subscriber( $subscriber );
					}
				} catch ( Exception $e ) {
					// Do not subscribe.
				}
			}

			/** @var array<\SimplePay\Vendor\League\Container\ServiceProvider\ServiceProviderInterface> $child_service_providers */
			$child_service_providers = $service_provider->get_service_providers();

			// Walk through child service providers.
			if ( ! empty( $child_service_providers ) ) {
				$this->add_service_providers( $child_service_providers );
			}
		}
	}

	/**
	 * Retrieves service providers for the derived context.
	 *
	 * @since 4.4.0
	 *
	 * @return array<\SimplePay\Vendor\League\Container\ServiceProvider\ServiceProviderInterface>
	 */
	private function get_service_providers() {
		global $wp_version;

		$service_providers = array(
			new AdminBar\AdminBarServiceProvider,
			new AntiSpam\AntiSpamServiceProvider,
			new CustomerSuccess\CustomerSuccessServiceProvider,
			new FormPreview\FormPreviewServiceProvider,
			new Integration\IntegrationServiceProvider,
			new License\LicenseServiceProvider,
			new Connect\ConnectServiceProvider,
			new PaymentPage\PaymentPageServiceProvider,
			new RestApi\RestApiServiceProvider,
			new StripeConnect\StripeConnectServiceProvider,
			new Transaction\TransactionServiceProvider,
			new User\UserServiceProvider,
			new Webhook\WebhookServiceProvider,
		);

		if ( version_compare( $wp_version, '5.6', '>=' ) ) {
			$service_providers[] = new Block\BlockServiceProvider;
		}

		if ( version_compare( $wp_version, '5.7', '>=' ) ) {
			$service_providers[] = new Help\HelpServiceProvider;
			$service_providers[] = new NotificationInbox\NotificationInboxServiceProvider;
		}

		if ( is_admin() ) {
			global $wp_version;

			$admin_service_providers = array(
				new Admin\AdminServiceProvider,
				new Admin\Addon\AddonServiceProvider,
				new Admin\DashboardWidget\DashboardWidgetServiceProvider,
				new Admin\Education\EducationServiceProvider,
				new Admin\SiteHealth\SiteHealthServiceProvider,
			);

			if ( version_compare( $wp_version, '5.5', '>=' ) ) {
				$admin_service_providers[] =
					new Admin\SetupWizard\SetupWizardServiceProvider;
			}

			if ( version_compare( $wp_version, '5.6', '>=' ) ) {
				$admin_service_providers[] =
					new Admin\FormBuilder\FormBuilderServiceProvider;
			}

			return array_merge( $admin_service_providers, $service_providers );
		}

		return $service_providers;
	}

}
