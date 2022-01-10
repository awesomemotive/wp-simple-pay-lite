<?php
/**
 * Utils: Exceptions
 *
 * @link https://github.com/stripe/stripe-php
 *
 * @package SimplePay\Core\Payments
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.9.0
 */

namespace SimplePay\Core\Utils;

use SimplePay\Core\i18n;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles a thrown Exception, possibly from Stripe.
 *
 * @since 3.9.0
 *
 * @param \Exception $e Exception.
 */
function handle_exception_message( $e ) {
	// Stripe Exception.
	if ( $e instanceof \SimplePay\Vendor\Stripe\Exception\ApiErrorException ) {
		$error = $e->getError();

		// Base instances of `\SimplePay\Vendor\Stripe\Exception\ApiErrorException`
		// does not contain specific error information.
		if ( null === $error ) {
			return $e->getMessage();
		}

		$code    = $error->code ? $error->code : '';
		$message = $error->message ? $error->message : $e->getMessage();

		return i18n\get_localized_error_message( $code, $message );
	}

	// Standard Exception.
	return $e->getMessage();
}
