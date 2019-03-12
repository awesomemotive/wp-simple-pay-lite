<?php

namespace SimplePay\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Objects factory.
 *
 * Helper class to get the right type of object used across the plugin.
 *
 * @since 3.0.0
 */
class Objects {

	/**
	 * Constructor.
	 *
	 * Add default objects.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$is_admin = is_admin();

		do_action( 'simpay_load_objects', $is_admin );
	}

	/**
	 * Get a specific form
	 *
	 * @param $object Post ID or post object
	 *
	 * @return false|null|Object
	 */
	public function get_form( $object ) {

		if ( is_int( $object ) ) {
			$object = get_post( $object );
		}
	
		if ( $object ) {
			return $this->get_object( apply_filters( 'simpay_form_object_type', 'default-form' ), 'form', $object );
		}

		return null;
	}

	/**
	 * Get a field.
	 *
	 * @since  3.0.0
	 *
	 * @param  array  $args Field args.
	 * @param  string $name Field type.
	 *
	 * @return null|Object
	 */
	public function get_field( $args, $name = '' ) {

		if ( empty( $name ) ) {
			$name = isset( $args['type'] ) ? $args['type'] : false;
		}

		return $name ? $this->get_object( $name, 'field', $args ) : null;
	}

	/**
	 * Get admin pages.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function get_admin_pages() {
		return apply_filters( 'simpay_get_admin_pages', array(
			'settings' => array(
				'keys',
				'general',
				'display',
			)
		) );
	}

	/**
	 * Get a settings page.
	 *
	 * @since  3.0.0
	 *
	 * @param  string $name
	 *
	 * @return null|Object
	 */
	public function get_admin_page( $name ) {
		return $name ? $this->get_object( $name, 'admin-page' ) : null;
	}

	/**
	 * Get a plugin object.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @param  string $name Object name.
	 * @param  string $type Object type.
	 * @param  mixed  $args (optional) arguments for the class constructor.
	 *
	 * @return null|false|Object
	 */
	private function get_object( $name, $type, $args = '' ) {

		$types = array(
			'admin-page',
			'field',
			'form',
		);

		if ( in_array( $type, $types ) ) {

			$class_name = $this->make_class_name( $name, $type );
			$parent     = '\\' . __NAMESPACE__ . '\Abstracts\\' . implode( '_', array_map( 'ucfirst', explode( '-', $type ) ) );
			$class      = class_exists( $class_name ) ? new $class_name( $args ) : false;

			return $class instanceof $parent ? $class : null;
		}

		return null;
	}

	/**
	 * Make class name from slug.
	 *
	 * Standardizes object naming and class names: <object-name> becomes <Class_Name>.
	 * The plugin autoloader uses a similar pattern.
	 *
	 * @since  3.0.0
	 * @access private
	 *
	 * @param  string $name Object name.
	 * @param  string $type Object type.
	 *
	 * @return string The class name complete with its full namespace.
	 */
	private function make_class_name( $name, $type ) {

		if ( 'field' == $type ) {
			$namespace = '\\' . __NAMESPACE__ . '\Admin\Fields\\';
		} elseif ( 'admin-page' == $type ) {
			$namespace = '\\' . __NAMESPACE__ . '\Admin\Pages\\';
		} elseif ( 'form' == $type ) {
			$namespace = '\\' . apply_filters( 'simpay_form_namespace', __NAMESPACE__ ) . '\Forms\\';
		} else {
			return '';
		}

		$class_name = implode( '_', array_map( 'ucfirst', explode( '-', $name ) ) );

		$final = apply_filters( 'simpay_object_loader', ( $namespace . $class_name ), $type, $namespace, $class_name );

		return $final;
	}
}
