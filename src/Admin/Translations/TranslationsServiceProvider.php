<?php
/**
 * Translations: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.9.0
 */

namespace SimplePay\Core\Admin\Translations;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * TranslationsServiceProvider class.
 *
 * @since 4.9.0
 */
class TranslationsServiceProvider extends AbstractPluginServiceProvider {

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
			'translation-language-packs',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Connection.
		$container->share(
			'translation-language-packs',
			LanguagePacks::class
		);
	}

}
