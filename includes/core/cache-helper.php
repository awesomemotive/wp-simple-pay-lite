<?php

namespace SimplePay\Core;

class Cache_Helper {

	public function __construct() {
		add_action( 'init', array( $this, 'stop_caching' ), 0 );

	}

	/**
	 * Loop through a list of URIs and if we are on that page then set the nocache constants
	 */
	public function stop_caching() {

		$excluded_uris = apply_filters( 'simpay_cache_exclusion_uris', array(
			'payment-confirmation',
			'payment-failed',
		) );

		if ( is_array( $excluded_uris ) && ! empty( $excluded_uris ) ) {

			foreach ( $excluded_uris as $uri ) {
				if ( stristr( trailingslashit( $_SERVER['REQUEST_URI'] ), $uri ) ) {
					$this->set_nocache();
				}
			}
		}
	}

	/**
	 * Set the nocache constants to true
	 */
	public function set_nocache() {

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( 'DONOTCACHEOBJECT', true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( 'DONOTCACHEDB', true );
		}

		nocache_headers();
	}
}
