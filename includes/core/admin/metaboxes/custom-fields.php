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
	 * @return array
	 */
	public static function get_fields( $post_id ) {
		$fields = get_post_meta( $post_id, '_custom_fields', true );

		if ( ! $fields ) {
			$fields = array();
		}

		return $fields;
	}

	/**
	 * Sort the custom fields by their order.
	 *
	 * @since 3.6.0
	 *
	 * @param $arr
	 * @return array
	 */
	public static function sort( $arr ) {

		// If our array is empty then exit now
		if ( empty( $arr ) ) {
			return $arr;
		}

		$fields     = $arr;
		$new_fields = array();
		$order      = array();

		if ( is_array( $fields ) ) {
			foreach ( $fields as $key => $row ) {

				if ( is_array( $row ) ) {
					foreach ( $row as $k => $v ) {

						$order[] = isset( $v['order'] ) ? $v['order'] : 9999;

						$v['type']    = $key;
						$new_fields[] = $v;

					}
				}
			}
		}

		array_multisort( $order, SORT_ASC, $new_fields );

		return $new_fields;
	}

	/**
	 * Extract the setting value from a custom field.
	 *
	 * Assumes there is only one instance of the field saved.
	 *
	 * @since 3.6.0
	 *
	 * @param array  $custom_fields Custom fields to search.
	 * @param string $field_type Custom Field type.
	 * @param string $setting Custom field setting.
	 * @param string $default Default setting value.
	 * @return mixed
	 */
	public static function extract_setting( $custom_fields, $field_type, $setting, $default = '' ) {
		if ( empty( $custom_fields ) ) {
			return $default;
		}

		foreach ( $custom_fields as $k => $field ) {
			if ( $field_type === $k ) {
				return isset( $field[0][ $setting ] ) ? $field[0][ $setting ] : $default;
			}
		}

		return $default;
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
