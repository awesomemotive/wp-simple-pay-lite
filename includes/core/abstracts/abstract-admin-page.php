<?php
/**
 * Admin page
 *
 * @package SimplePay\Core\Abstracts
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.0.0
 */

namespace SimplePay\Core\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The Admin Page.
 *
 * @since 3.0.0
 */
abstract class Admin_Page {

	/**
	 * Admin page ID.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Option group.
	 *
	 * @var string
	 */
	public $option_group = '';

	/**
	 * Admin Page label.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Admin Page description.
	 *
	 * @access public
	 * @var string
	 */
	public $description = '';

	/**
	 * Admin Page settings sections.
	 *
	 * @var array Associative array with section id (key) and section name (value)
	 */
	public $sections;

	/**
	 * Admin Page settings fields.
	 *
	 * @access public
	 * @var array
	 */
	public $fields;

	/**
	 * Saved values.
	 *
	 * @access protected
	 * @var array
	 */
	protected $values = array();

	/**
	 * Docs link text.
	 *
	 * @var string
	 */
	public $link_text = '';

	/**
	 * Docs link slug.
	 *
	 * @var string
	 */
	public $link_slug = '';

	/**
	 * Docs link Google Analytics content.
	 *
	 * @var string
	 */
	public $ga_content = '';


	/**
	 * Get admin page settings.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	public function get_settings() {

		$settings = array();

		$settings[ $this->id ] = array(
			'label'       => $this->label,
			'description' => $this->description,
			'link_text'   => $this->link_text,
			'link_slug'   => $this->link_slug,
			'ga_content'  => $this->ga_content,
		);

		if ( ! empty( $this->sections ) && is_array( $this->sections ) ) {

			foreach ( $this->sections as $section => $content ) {

				$section = sanitize_key( $section );

				$settings[ sanitize_key( $this->id ) ]['sections'][ $section ] = array(
					'title'       => isset( $content['title'] ) ? sanitize_text_field( $content['title'] ) : '',
					'description' => isset( $content['description'] ) ? sanitize_text_field( $content['description'] ) : '',
					'callback'    => array( $this, 'add_settings_section_callback' ),
					'fields'      => isset( $this->fields[ $section ] ) ? $this->fields[ $section ] : '',
				);

			}
		}

		return apply_filters( 'simpay_get_' . $this->option_group . '_' . $this->id, $settings );
	}

	/**
	 * Get option value.
	 *
	 * @since  3.0.0
	 * @access protected
	 *
	 * @param string $section Option section.
	 * @param string $setting Setting name.
	 * @return string
	 */
	protected function get_option_value( $section, $setting ) {

		$option = $this->values;

		if ( ! empty( $option ) && is_array( $option ) ) {
			return isset( $option[ $section ][ $setting ] ) ? $option[ $section ][ $setting ] : '';
		}

		return '';
	}

	/**
	 * Add sections for this page.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	abstract public function add_sections();

	/**
	 * Get settings fields.
	 *
	 * @since  3.0.0
	 *
	 * @return array
	 */
	abstract public function add_fields();

	/**
	 * Default basic callback for page sections.
	 *
	 * @since  3.0.0
	 *
	 * @param array $section Setting section data.
	 */
	public function add_settings_section_callback( $section ) {

		$callback    = isset( $section['callback'][0] ) ? $section['callback'][0] : '';
		$sections    = isset( $callback->sections ) ? $callback->sections : '';
		$description = isset( $sections[ $section['id'] ]['description'] ) ? $sections[ $section['id'] ]['description'] : '';
		$default     = $description ? '<p>' . $description . '</p>' : '';

		echo apply_filters( 'simpay_' . $this->option_group . '_' . $this->id . '_sections_callback', $default );
	}

	/**
	 * Register setting callback.
	 *
	 * Callback function for sanitizing and validating options before they are updated.
	 *
	 * @todo Properly handle arrays for all types, not just payment_confirmation_messages
	 *
	 * @since  3.0.0
	 *
	 * @param  array $settings Settings inputs.
	 *
	 * @return array Sanitized settings.
	 */
	public function validate( $settings ) {

		$sanitized = array();

		if ( is_array( $settings ) ) {
			foreach ( $settings as $k => $v ) {
				if ( 'payment_confirmation_messages' == $k ) {

					// @link https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1142
					if ( is_array( $v ) ) {
						foreach ( $v as $setting_key => $setting_value ) {
							$sanitized[ $k ][ $setting_key ] = wp_kses_post( $setting_value );
						}
					} else {
						$sanitized[ $k ] = wp_kses_post( $v );
					}
				} else {
					$sanitized[ $k ] = simpay_sanitize_input( $v );
				}
			}
		} else {
			$sanitized = simpay_sanitize_input( $settings );
		}

		return $sanitized;
	}

}
