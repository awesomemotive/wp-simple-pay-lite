<?php
/**
 * Handles getting and setting style meta values.
 *
 * @package SimplePay\Core\Admin\FormBuilder\FormStyle
 */

namespace SimplePay\Core\Admin\FormBuilder\FormStyle;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings handler class.
 *
 * Handles getting, setting, and managing style meta values for forms.
 * Provides methods for retrieving, saving, and deleting style settings,
 * as well as checking if settings exist and sanitizing values.
 *
 * @since 4.17.0
 */
class Settings {

	/**
	 * The prefix for all style meta keys.
	 *
	 * @var string
	 */
	private static $meta_prefix = '_wpsp_style_';

	/**
	 * Get a specific style setting for a form.
	 *
	 * @since 4.17.0
	 *
	 * @param int    $form_id The WP Simple Pay form ID (Post ID).
	 * @param string $key     The setting key (e.g., 'primary_color').
	 * @param string $default Optional. The default value if the setting is not found.
	 * @return string The setting value.
	 */
	public static function get_setting( $form_id, $key, $default = '' ) {
		$meta_key = self::$meta_prefix . sanitize_key( $key );
		$value    = get_post_meta( $form_id, $meta_key, true );

		$value = self::sanitize_setting( $key, $value );

		// For border radius settings, treat empty and '0' consistently.
		if ( in_array( $key, array( 'border_radius', 'form_border_radius' ), true ) && '0' === $value && '' === $default ) {
			$default = '0';
		}

		return ( '' !== $value ) ? $value : $default;
	}

	/**
	 * Get the raw setting value without default fallback.
	 *
	 * @since 4.17.0
	 *
	 * @param int    $form_id The WP Simple Pay form ID (Post ID).
	 * @param string $key     The setting key (e.g., 'primary_color').
	 * @return string The raw setting value (can be empty string).
	 */
	public static function get_raw_setting( $form_id, $key ) {
		$meta_key = self::$meta_prefix . sanitize_key( $key );
		$value    = get_post_meta( $form_id, $meta_key, true );

		if ( '' === $value || false === $value ) {
			return '';
		}

		return self::sanitize_setting( $key, $value );
	}

	/**
	 * Save a specific style setting for a form.
	 *
	 * @since 4.17.0
	 *
	 * @param int    $form_id The WP Simple Pay form ID (Post ID).
	 * @param string $key     The setting key (e.g., 'primary_color').
	 * @param mixed  $value   The value to save.
	 * @return bool|int Meta ID if the key didn't exist, true on successful update, false on failure.
	 */
	public static function save_setting( $form_id, $key, $value ) {
		$meta_key = self::$meta_prefix . sanitize_key( $key );

		$value = self::sanitize_setting( $key, $value );

		return update_post_meta( $form_id, $meta_key, $value );
	}

	/**
	 * Get all style setting keys.
	 *
	 * Returns an array of all available style setting keys that can be
	 * used with get_setting() and save_setting() methods.
	 *
	 * @since 4.17.0
	 *
	 * @return array<string> Array of style setting keys.
	 */
	public static function get_style_keys(): array {
		return array(
			'selected_theme',
			'form_container_background_color',
			'background_color',
			'text_color',
			'label_text_color',
			'input_text_color',
			'border_color',
			'primary_color',
			'button_background_color',
			'button_text_color',
			'button_hover_background_color',
			'border_radius',
			'form_border_radius',
			'form_padding',
			'label_font_size',
			'label_font_weight',
			'input_font_size',
			'error_border_color',
			'error_text_color',
			'title_color',
			'title_font_size',
			'title_font_weight',
			'description_color',
			'description_font_size',
			'description_font_weight',
		);
	}

	/**
	 * Delete a specific style setting for a post.
	 *
	 * Removes a style setting from the database for the specified post.
	 *
	 * @since 4.17.0
	 *
	 * @param int    $post_id The post ID.
	 * @param string $key     The setting key (without prefix).
	 * @return void
	 */
	public static function delete_setting( $post_id, $key ) {
		delete_post_meta( $post_id, self::$meta_prefix . sanitize_key( $key ) );
	}

	/**
	 * Check if a specific style setting exists for a post.
	 *
	 * Determines if a style setting has been saved for the specified post.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id The post ID.
	 * @param string $key     The setting key (without prefix).
	 * @return bool True if the meta key exists, false otherwise.
	 */
	public static function setting_exists( $post_id, $key ) {
		return metadata_exists( 'post', $post_id, self::$meta_prefix . sanitize_key( $key ) );
	}

	/**
	 * Sanitize a setting value.
	 *
	 * Sanitizes a setting value based on its key to ensure data integrity
	 * and security before use.
	 *
	 * @since 4.17.0
	 *
	 * @param string $key   Setting key.
	 * @param mixed  $value Setting value.
	 * @return string Sanitized setting value.
	 */
	public static function sanitize_setting( $key, $value ) {
		$value = is_scalar( $value ) ? (string) $value : '';

		switch ( $key ) {
			case 'selected_theme':
				return sanitize_text_field( $value );
			case 'form_container_background_color':
			case 'background_color':
			case 'text_color':
			case 'label_text_color':
			case 'input_text_color':
			case 'border_color':
			case 'primary_color':
			case 'button_background_color':
			case 'button_text_color':
			case 'button_hover_background_color':
			case 'error_border_color':
			case 'error_text_color':
			case 'title_color':
			case 'description_color':
				// Handle empty values.
				if ( empty( $value ) || '' === trim( $value ) ) {
					return '';
				}
				// Try to sanitize as hex color first.
				$hex_sanitized = sanitize_hex_color( $value );
				// If sanitize_hex_color returns a value, use it.
				if ( ! empty( $hex_sanitized ) ) {
					return $hex_sanitized;
				}
				// Validate rgba/rgb/hsla/hsl color formats to prevent CSS injection.
				if ( preg_match( '/^(rgba?|hsla?)\(\s*[\d.,\s%]+\)$/', $value ) ) {
					return sanitize_text_field( $value );
				}
				// Reject any other value to prevent CSS injection.
				return '';
			case 'border_radius':
			case 'form_border_radius':
			case 'form_padding':
			case 'label_font_size':
			case 'input_font_size':
			case 'title_font_size':
			case 'description_font_size':
				if ( '' === trim( $value ) ) {
					return '';
				}

				return (string) absint( $value );
			case 'label_font_weight':
			case 'title_font_weight':
			case 'description_font_weight':
				$allowed_values = array( '', 'normal', 'bold', '100', '200', '300', '400', '500', '600', '700', '800', '900' );
				return in_array( $value, $allowed_values, true ) ? $value : '';
			default:
				return $value;
		}
	}
}
