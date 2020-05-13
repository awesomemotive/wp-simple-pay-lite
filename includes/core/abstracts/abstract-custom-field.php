<?php
/**
 * Custom field
 *
 * @package SimplePay\Core\Abstracts
 * @copyright Copyright (c) 2019, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Custom_Field
 *
 * @todo Don't use a static methods and use a proper constructor.
 */
abstract class Custom_Field {

	/**
	 * Field type.
	 *
	 * @since 3.7.0
	 * @var string
	 */
	private static $type;

	/**
	 * Field settings.
	 *
	 * @since 3.7.0
	 * @var array
	 */
	private static $settings = array();

	/**
	 * Form.
	 *
	 * @since 3.7.0
	 * @var SimplePay\Core\Abstracts\Form
	 */
	protected static $form;

	/**
	 * Static function that we can call from any field and it will in turn call
	 * the correct static function for that field's HTML output.
	 *
	 * @since 3.0.0
	 * @since 3.7.0 Passes field type as second arugment.
	 * @since 3.7.0 Passes form instance as third argument.
	 *
	 * @param array                         $settings Field settings.
	 * @param string                        $type Field type.
	 * @param SimplePay\Core\Abstracts\Form $form Form.
	 * @return string
	 */
	public static function html( $settings, $type = null, $form = null ) {
		self::$settings = $settings;
		self::$type     = $type;
		self::$form     = $form;

		return static::print_html( $settings, $type, $form );
	}

	/**
	 * Returns a filterable default value.
	 *
	 * @since 3.7.0
	 *
	 * @param string $key Key that stores the default value.
	 * @param mixed  $fallback Fallback value. Defaults to empty string.
	 * @return mixed
	 */
	public static function get_default_value( $key = 'default', $fallback = '' ) {
		$id = isset( self::$settings['uid'] )
			? self::$settings['uid']
			: '';

		$default = isset( self::$settings[ $key ] )
			? self::$settings[ $key ]
			: $fallback;

		/**
		 * Filters the default value used on a custom field.
		 *
		 * @since 3.7.0
		 *
		 * @param mixed  $default Default value. Empty string if nothing is set.
		 * @param string $id Field ID.
		 * @param SimplePay\Core\Abstracts\Form
		 * @param string $type Field type.
		 */
		$default = apply_filters(
			'simpay_form_field_default_value',
			$default,
			$id,
			self::$form,
			self::$type
		);

		$default = simpay_get_filtered( 'field_' . $id . '_default_value', $default, self::$form->id );

		return $default;
	}
}
