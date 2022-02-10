<?php
/**
 * Setup Wizard: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\Admin\SetupWizard;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * SetupWizardServiceProvider class.
 *
 * @since 4.4.2
 */
class SetupWizardServiceProvider extends AbstractPluginServiceProvider {

	/*
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
			'setup-wizard-launch',
			'setup-wizard-marketing',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Launcher.
		$container->share(
			'setup-wizard-launch',
			SetupWizardLaunch::class
		);

		// Marketing.
		$container->share(
			'setup-wizard-marketing',
			SetupWizardMarketing::class
		);
	}

}
