<?php
/**
 * Custom field
 *
 * @package SimplePay\Core\Abstracts
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
	protected static $type;

	/**
	 * Field settings.
	 *
	 * @since 3.7.0
	 * @var array
	 */
	protected static $settings = array();

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
	 * Creates and returns an ID that can be used as an HTML attribute.
	 *
	 * @example simpay-form-{$form_id}-field-{$field_id}
	 *
	 * @since 3.9.0
	 *
	 * @return string
	 */
	public static function get_id_attr() {
		$id = isset( self::$settings['uid'] )
			? self::$settings['uid']
			: '';

		$id = 'simpay-form-' . self::$form->id . '-field-' . $id;

		return $id;
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

	/**
	 * Returns the field's label.
	 *
	 * @since 3.9.0
	 *
	 * @param string $label_setting_key Setting key to look for the label.
	 * @param string $label_fallback Fallback label value.
	 * @return string
	 */
	public static function get_label( $label_setting_key = 'label', $label_fallback = '' ) {
		$id = self::get_id_attr();

		$label = isset( self::$settings[ $label_setting_key ] )
			? self::$settings[ $label_setting_key ]
			: $label_fallback;

		$classes = array();

		if ( empty( $label ) ) {
			$label     = self::$settings['type'];
			$classes[] = 'screen-reader-text';
		}

		ob_start();
		?>

		<div class="simpay-<?php echo esc_attr( self::$settings['type'] ); ?>-label simpay-label-wrap">
			<label for="<?php echo esc_attr( $id ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
				<?php echo $label; // WPCS: XSS okay. ?>
				<?php
				switch ( self::$type ) :
					case 'email':
					case 'radio':
					case 'card':
					case 'address':
					case 'custom_amount':
					case 'plan_select':
						$required = true;
						break;
					default:
						$required = isset( self::$settings['required'] );
				endswitch;

				if ( true === $required ) :
					echo ''; // WPCS: XSS okay.
				else :
					echo self::get_optional_indicator(); // WPCS: XSS okay.
				endif;
				?>
			</label>
		</div>

		<?php
		return ob_get_clean();
	}

	/**
	 * Returns an indicator if the field is optional.
	 *
	 * @since 3.9.0
	 *
	 * @return string Required indicator, or empty string if the field is not optional.
	 */
	public static function get_optional_indicator() {
		$optional_indicator = '<span class="simpay-optional-indicator">' . esc_html__( ' (optional)', 'stripe' ) . '</span>';

		/**
		 * Filters the indicator for optional fields.
		 *
		 * @since 3.9.0
		 *
		 * @param string $indicator Required indicator.
		 */
		$optional_indicator = apply_filters( 'simpay_form_field_optional_indicator', $optional_indicator );

		return $optional_indicator;
	}
}
