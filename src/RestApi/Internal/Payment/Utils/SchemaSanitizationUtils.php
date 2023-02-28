<?php
/**
 * Utils: Schema sanitization
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.0
 */

namespace SimplePay\Core\RestApi\Internal\Payment\Utils;

/**
 * SchemaSanitizationUtils class.
 *
 * @since 4.7.0
 */
class SchemaSanitizationUtils {

	/**
	 * Sanitizes the `form_values` parameter for the given request.
	 *
	 * @since 4.7.0
	 *
	 * @param array<string, string|array<string, string>> $value The `form_values` parameter value.
	 * @return array<string, string|array<string, string>> The sanitized form values.
	 */
	public static function sanitize_form_values_arg( $value ) {
		foreach ( $value as $key => $val ) {
			$value[ sanitize_text_field( $key ) ] = is_array( $val )
				? array_map( 'sanitize_text_field', $val )
				: sanitize_text_field( $val );
		}

		return $value;
	}

}
