<?php
/**
 * Cache helper
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache_Helper class
 *
 * @since 3.0.0
 */
class Cache_Helper {

	/**
	 * Hooks in to WordPress.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'stop_caching' ), 0 );
	}

	/**
	 * Sets nocache headers for specific pages.
	 *
	 * @since 3.0.0
	 */
	public function stop_caching() {
		/**
		 * Filters the page slugs to set nocache headers.
		 *
		 * @since 3.0.0
		 *
		 * @param array $page_slugs Page slugs.
		 */
		$excluded_uris = apply_filters(
			'simpay_cache_exclusion_uris',
			array(
				'payment-confirmation',
				'payment-failed',
			)
		);

		if ( is_array( $excluded_uris ) && ! empty( $excluded_uris ) ) {
			foreach ( $excluded_uris as $uri ) {
				if ( stristr( trailingslashit( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), $uri ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
					$this->set_nocache();
				}
			}
		}
	}

	/**
	 * Sets the nocache constants to true.
	 *
	 * @since 3.0.0
	 */
	public function set_nocache() {
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound
		}

		nocache_headers();
	}
}
