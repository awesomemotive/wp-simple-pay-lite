<?php
/**
 * Form preview: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.2
 */

namespace SimplePay\Core\FormPreview;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * FormPreviewServiceProvider class.
 *
 * @since 4.4.2
 */
class FormPreviewServiceProvider extends AbstractPluginServiceProvider {

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
			'form-preview-output',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		$container->share(
			'form-preview-output',
			FormPreviewOutput::class
		);
	}

}
