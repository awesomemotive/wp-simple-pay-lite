<?php
/**
 * REST API functionality.
 *
 * @since 3.5.0
 */

namespace SimplePay\Core\REST_API;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get available REST API versions.
 *
 * @since 3.5.0
 *
 * @return array
 */
function get_versions() {
	return array(
		'v1',
		'v2',
	);
}

/**
 * Return the current version of the REST API.
 *
 * @since 3.5.0
 *
 * @return string
 */
function get_current_version() {
	$versions = get_versions();
	$latest   = end( $versions );

	/**
	 * Filter the version of the REST API to use.
	 * Default is the latest.
	 *
	 * @since 3.5.0
	 *
	 * @param string $version Current version of the REST API.
	 */
	return apply_filters( 'simpay_get_rest_api_version', $latest );
}
