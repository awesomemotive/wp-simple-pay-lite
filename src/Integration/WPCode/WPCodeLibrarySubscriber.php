<?php
/**
 * WPCode: Library
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.11
 */

namespace SimplePay\Core\Integration\WPCode;

use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * WPCodeLibrarySubscriber class.
 *
 * @since 4.4.3
 */
class WPCodeLibrarySubscriber implements SubscriberInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'load-code-snippets_page_wpcode-snippet-manager' => 'register',
			'load-code-snippets_page_wpcode-library' => 'register',
		);
	}

	/**
	 * Registers the wpsimplepay WPCode library.
	 *
	 * @since 4.7.11
	 *
	 * @return void
	 */
	public function register() {
		if ( ! function_exists( 'wpcode_register_library_username' ) ) {
			return;
		}

		wpcode_register_library_username(
			'wpsimplepay',
			'WP Simple Pay',
			SIMPLE_PAY_VERSION // @phpstan-ignore-line
		);
	}

}
