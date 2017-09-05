<?php

namespace SimplePay\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Custom_Field {

	/**
	 * Custom_Field constructor.
	 */
	public function __construct() {
		// No constructor needed, but to keep consistent will keep it here but just blank
	}

	/**
	 * Static function that we can call from any field and it will in turn call the correct static function for that
	 * field's HTML output
	 *
	 * @param $settings
	 *
	 * @return string
	 */
	public static function html( $settings ) {
		return static::print_html( $settings );
	}
}
