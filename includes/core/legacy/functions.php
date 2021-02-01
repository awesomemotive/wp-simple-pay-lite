<?php
// phpcs:ignoreFile
/**
 * Legacy: functions
 *
 * @package SimplePay\Core
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get a specific form setting.
 *
 * @since 3.0.0
 * @deprecated 4.0.0 Use simpay_get_setting()
 *
 * @param string   $setting Setting key.
 * @param null|int $form_id Form ID.
 * @return false
 */
function simpay_get_form_setting( $setting, $form_id = null ) {
	_doing_it_wrong(
		__FUNCTION__,
		__( 'Access form properties directly', 'stripe' ),
		'4.0.0'
	);

	return false;
}

/**
 * Returns a setting value.
 *
 * @param string $setting Setting key.
 * @param bool   $raw If the value should be unfiltered. Default true.
 * @return null|mixed
 */
function simpay_get_global_setting( $setting, $raw = false ) {
	_doing_it_wrong(
		__FUNCTION__,
		__( 'Use simpay_get_setting() directly.', 'stripe' ),
		'4.0.0'
	);

	return simpay_get_setting( $setting, null, $raw );
}

/**
 * Return the total amount for the form.
 *
 * @param bool $formatted
 *
 * @return string
 */
function simpay_get_total( $formatted = true ) {
	_doing_it_wrong(
		__FUNCTION__,
		__( 'Access amount through form properties.', 'stripe' ),
		'4.0.0'
	);

	return null;
}

/**
 * Get the default editor content based on what type of editor is passed in
 *
 * @param $editor
 *
 * @return mixed|string
 */
function simpay_get_editor_default( $editor ) {
	return '';
}
