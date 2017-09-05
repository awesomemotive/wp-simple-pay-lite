<?php

namespace SimplePay\Core\Admin\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class that contains everything for the custom fields meta boxes UI
 */
class Custom_Fields {

	/**
	 * Custom_Fields constructor.
	 */
	public function __construct(  ) {
	}

	/**
	 * Get the custom fields post meta
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_fields( $post_id ) {

		$fields = get_post_meta( $post_id, '_custom_fields', true );

		return $fields;
	}

	/**
	 * Get the available custom field options
	 *
	 * @return mixed
	 */
	public static function get_options() {

		return apply_filters( 'simpay_custom_field_options', array(
			'payment_button'          => array(
				'label' => esc_html__( 'Payment Button', 'stripe' ),
				'type'  => 'payment_button',
			),
		) );
	}

}
