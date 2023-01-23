<?php
/**
 * Help: Service provider
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\Help;

use SimplePay\Core\AbstractPluginServiceProvider;

/**
 * HelpServiceProvider class.
 *
 * @since 4.4.6
 */
class HelpServiceProvider extends AbstractPluginServiceProvider {

	/*
	 * {@inheritdoc}
	 */
	public function get_services() {
		return array(
			'admin-help-docs-context',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribers() {
		return array(
			'admin-help-docs-importer',
			'admin-help-ui',
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function register() {
		$container = $this->getContainer();

		// Docs importer.
		$container->share(
			'admin-help-docs-importer',
			HelpDocsImporter::class
		)
			->withArgument( $this->get_remote_api_url() )
			->withArgument( $container->get( 'scheduler' ) );

		// Context.
		$container->share(
			'admin-help-docs-context',
			HelpDocsContext::class
		);

		// UI.
		$container->share( 'admin-help-ui', HelpUi::class )
			->withArgument( $container->get( 'admin-help-docs-context' ) );
	}

	/**
	 * Returns the API URL for remote doc articles to import.
	 *
	 * @since 4.4.6
	 *
	 * @return string
	 */
	private function get_remote_api_url() {
		if ( defined( 'SIMPAY_HELP_DOCS_API_URL' ) ) {
			return SIMPAY_HELP_DOCS_API_URL;
		}

		return 'https://wpsimplepay.com/wp-content/docs.json';
	}

}
