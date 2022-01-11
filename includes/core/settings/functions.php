<?php
/**
 * Settings: Functions
 *
 * @package SimplePay\Core\Settings
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

namespace SimplePay\Core\Settings;

use SimplePay\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the first/main section ID.
 *
 * @since 4.0.0
 *
 * @return string
 */
function get_main_section_id() {
	$sections = Utils\get_collection( 'settings-sections' );

	if ( false === $sections ) {
		return '';
	}

	return key( $sections->get_items() );
}

/**
 * Returns the first/main section ID.
 *
 * @since 4.0.0
 *
 * @param string $section Registered Section ID.
 * @return string
 */
function get_main_subsection_id( $section ) {
	$sections = Utils\get_collection( 'settings-sections' );

	if ( false === $sections ) {
		return '';
	}

	$section = $sections->get_item( $section );

	if ( false === $section ) {
		return '';
	}

	return key( $section->get_subsections() );
}

/**
 * Returns a URL for a specific section, subsection, or setting.
 *
 * @since 4.0.0
 *
 * @param array $args {
 *   Specific section, subsection, or setting. Each requires the one before it.
 *
 *   @type string $section Section ID.
 *   @type string $subsection Subsection ID.
 *   @type string $setting Setting ID.
 * }
 * @return string
 */
function get_url( $args = array() ) {
	$defaults = array(
		'section'    => false,
		'subsection' => false,
		'setting'    => false,
	);

	$args = wp_parse_args( $args, $defaults );

	// Base settings. Defaults to first section and subsection.
	$base = add_query_arg(
		array(
			'post_type' => 'simple-pay',
			'page'      => 'simpay_settings',
		),
		admin_url( 'edit.php' )
	);

	// Specificed section and subsection.
	$url = add_query_arg(
		array(
			'tab'        => $args['section'],
			'subsection' => $args['subsection'],
		),
		$base
	);

	// Append a setting ID if set.
	if ( ! empty( $args['setting'] ) ) {
		$url = $url . sprintf(
			'#simpay-settings-%s-%s-%s',
			$args['section'],
			$args['subsection'],
			$args['setting']
		);
	}

	return $url;
}
