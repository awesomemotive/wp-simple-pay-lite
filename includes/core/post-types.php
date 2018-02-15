<?php

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Types and Taxonomies.
 *
 * Register and initialize custom post types and custom taxonomies.
 *
 * @since 3.0.0
 */
class Post_Types {

	/**
	 * Hook in WordPress init to register custom content.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		// Register custom post types.
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
	}

	/**
	 * Register custom post types.
	 *
	 * @since 3.0.0
	 */
	public static function register_post_types() {

		do_action( 'simpay_register_post_types' );

		if ( ! post_type_exists( 'simple-pay' ) ) {

			$args = array(
				'capability_type' => 'post',
				'public'          => false,
				'show_ui'         => false,
			);

			register_post_type( 'simple-pay', $args );
		}

	}
}
