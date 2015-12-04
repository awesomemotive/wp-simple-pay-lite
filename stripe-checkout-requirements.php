<?php
/**
 * Stripe Checkout plugin requirements
 *
 * Utility class to check current PHP version and WordPress to meet plugin requirements.
 * Based on https://github.com/nekojira/wp-requirements
 */

if ( ! defined( 'WPINC' ) ) {
	exit;
}

/**
 * Plugin requirements.
 *
 * Checks for WordPress and PHP versions.
 */
class Stripe_Checkout_Requirements {

	/**
	 * WordPress.
	 *
	 * @access private
	 * @var bool
	 */
	private $wp = true;

	/**
	 * PHP.
	 *
	 * @access private
	 * @var bool
	 */
	private $php = true;

	/**
	 * PHP Extensions.
	 *
	 * @access private
	 * @var bool
	 */
	private $ext = true;

	/**
	 * Results failures.
	 *
	 * Associative array with requirements results.
	 *
	 * @access private
	 * @var array
	 */
	private $failures = array();

	/**
	 * Constructor.
	 *
	 * @param array $requirements Associative array with requirements.
	 */
	public function __construct( $requirements ) {

		if ( $requirements && is_array( $requirements ) ) {

			$errors       = array();
			$requirements = array_merge(
				array( 'wp' => '', 'php' => '', 'extensions' => array() ),
				$requirements
			);

			// Check for WordPress version.
			if ( $requirements['wp'] && is_string( $requirements['wp'] ) ) {
				global $wp_version;
				// If $wp_version isn't found or valid probably you are not running WordPress (properly)?
				$wp_ver = $wp_version && is_string( $wp_version ) ? $wp_version : $requirements['wp'];
				$wp_ver = version_compare( $wp_ver, $requirements['wp'] );
				if ( $wp_ver === -1 ) {
					$errors['wp'] = $wp_version;
					$this->wp = false;
				}
			}

			// Check fo PHP version.
			if ( $requirements['php'] && is_string( $requirements['php'] ) ) {
				$php_ver = version_compare( PHP_VERSION, $requirements['php'] );
				if ( $php_ver === -1 ) {
					$errors['php'] = PHP_VERSION;
					$this->php = false;
				}
			}

			if ( $requirements['ext'] && is_array( $requirements['ext'] ) ) {
				foreach ( $requirements['ext'] as $extension ) {
					if ( is_string( $extension ) ) {
						$extension = htmlspecialchars( trim( $extension ) );
						$loaded    = extension_loaded( $extension );
						$errors['ext'][ $extension ] = $loaded;
						if ( false === $loaded ) {
							$this->ext = false;
						}
					}
				}
			}

			$this->failures = $errors;

		} else {
			trigger_error( 'Stripe Checkout Requirements: the requirements requested are invalid.', E_USER_ERROR );
		}
	}

	/**
	 * Get requirements results.
	 *
	 * @return array
	 */
	public function failures() {
		return $this->failures;
	}

	/**
	 * Versions check pass.
	 *
	 * @return bool
	 */
	public function pass() {
		return in_array( false, array( $this->wp, $this->php, $this->ext ) ) ? false : true;
	}

}
