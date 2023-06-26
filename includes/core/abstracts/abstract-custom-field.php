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

use SimplePay\Core\Utils;

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
	 * @param array<string, mixed> $settings Field settings. Overrides current self if set.
	 * @param SimplePay\Core\Abstracts\Form|null $form Form. Overrides current self if set.
	 * @return mixed
	 */
	public static function get_default_value(
		$key = 'default',
		$fallback = '',
		$settings = array(),
		$form = null
	) {
		if ( ! empty( $settings ) ) {
			self::$settings = $settings;
		}

		if ( ! is_null( $form ) ) {
			self::$form = $form;
		}

		$id = isset( self::$settings['uid'] )
			? self::$settings['uid']
			: '';

		$default = isset( self::$settings[ $key ] )
			? self::$settings[ $key ]
			: $fallback;

		$default = self::get_dynamic_default_values( $default );

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

	/**
	 * Replaces dynamic value placeholders with their actual values.
	 *
	 * @since 4.7.6
	 *
	 * @param string $default Default value.
	 * @return string
	 */
	private static function get_dynamic_default_values( $default ) {
		// Replace any Smart Tags in the default value with their dynamic values.
		$tags = self::get_default_value_smart_tags();

		foreach ( $tags as $tag ) {
			// Standard {form-id}, {form-title}, etc. Smart Tags.
			if ( strpos( $default, $tag ) !== false ) {
				$default = str_replace(
					$tag,
					self::get_default_value_smart_tag_value( $tag ),
					$default
				);
			}
		}

		// Replace all instances of {query var=""} Smart Tag with the query var value.
		if ( strpos( $default, '{query var="' ) !== false ) {
			$pattern = '/\{query var="([^"]+)"\}/';
			$matches = [];

			if ( preg_match_all( $pattern, $default, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					$default = str_replace(
						$match,
						self::get_default_value_smart_tag_value( $match ),
						$default
					);
				}
			}
		}

		// Force an additional escape _just in case_ because we are accepting user input.
		// It should still be late-escaped on output.
		return esc_attr( $default );
	}

	/**
	 * Returns an array of dynamic value tags that may found in the default value.
	 *
	 * @since 4.7.6
	 *
	 * @param string $default_value Default value.
	 * @return array<string>
	 */
	private static function get_default_value_smart_tags() {
		return array(
			'{query var=""}',
			'{form-id}',
			'{form-title}',
			'{form-description}',
			'{page-id}',
			'{page-title}',
			'{page-url}',
			'{user-id}',
			'{user-email}',
			'{user-first-name}',
			'{user-last-name}',
			'{user-ip}',
		);
	}

	/**
	 * Returns the value of a dynamic value tag.
	 *
	 * @since 4.7.6
	 *
	 * @param string $smart_tag Smart Tag.
	 * @return string
	 */
	private static function get_default_value_smart_tag_value( $smart_tag ) {
		$is_query = strpos( $smart_tag, 'query' ) !== false;

		if ( $is_query ) {
			$pattern = '/\{query var="([^"]+)"\}/';
			$matches = [];

			if ( preg_match($pattern, $smart_tag, $matches ) ) {
				$query_var = esc_html( $matches[1] );

				if ( isset( $_GET[ $query_var ] ) ) {
					return esc_attr( $_GET[ $query_var ] );
				}
			}

			return '';
		} else {
			switch ( $smart_tag ) {
				case '{form-id}':
					return esc_attr( self::$form->id );
				case '{form-title}':
					return esc_attr( self::$form->company_name );
				case '{form-description}':
					return esc_attr( self::$form->item_description );
				case '{page-id}':
					return esc_attr( get_the_ID() );
				case '{page-title}':
					return esc_attr( get_the_title() );
				case '{page-url}':
					return esc_attr( get_permalink() );
				case '{user-id}':
					return esc_attr( get_current_user_id() );
				case '{user-email}':
					return esc_attr( wp_get_current_user()->user_email );
				case '{user-first-name}':
					return esc_attr( wp_get_current_user()->first_name );
				case '{user-last-name}':
					return esc_attr( wp_get_current_user()->last_name );
				case '{user-ip}':
					return esc_attr( Utils\get_current_ip_address() );
			}
		}

		return '';
	}
}
