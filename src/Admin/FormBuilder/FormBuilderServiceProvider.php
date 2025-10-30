<?php
/**
 * Form builder: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.3
 */

namespace SimplePay\Core\Admin\FormBuilder;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * FormBuilderServiceProvider class.
 *
 * @since 4.4.3
 */
class FormBuilderServiceProvider extends AbstractPluginServiceProvider {

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
			'form-builder-license-check',
			'form-builder-template-explorer',
			'form-builder-custom-field-subscriber',
			'form-builder-automator-integrations-importer',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// License check.
		$container->share(
			'form-builder-license-check',
			LicenseCheck::class
		);

		// Template explorer.
		$container->share(
			'form-builder-template-explorer',
			TemplateExplorer::class
		);

		// Custom field subscriber.
		$container->share(
			'form-builder-custom-field-subscriber',
			CustomFieldSubscriber::class
		);

		// Uncanny Automator integrations importer for the "Automations" tab.
		$container->share(
			'form-builder-automator-integrations-importer',
			AutomatorIntegrationsImporter::class
		)
			->withArgument( 'https://integrations.automatorplugin.com/list.json' )
			->withArgument( $container->get( 'scheduler' ) );
	}

}
