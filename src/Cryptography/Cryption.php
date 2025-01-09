<?php
/**
 * Cryptography: Cryption
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.12.1
 */

namespace SimplePay\Core\Cryptography;

use Exception;

/**
 * Cryption class.
 *
 * @since 4.12.1
 */
class Cryption {

	/**
	 * Encrypts data using RSA public key.
	 *
	 * @param array<string, mixed> $data Data to encrypt.
	 * @return string|bool Base64 encoded encrypted data or false on failure.
	 */
	public function encrypt( $data ) {
		// Check if openssl is enabled.
		if ( ! extension_loaded( 'openssl' ) ) {
			return false;
		}

		try {
			// Get the public key directly from the file.
			$public_key = file_get_contents(
				plugin_dir_path( SIMPLE_PAY_MAIN_FILE ) . 'data/etc/public_key.pem' // @phpstan-ignore-line
			);

			if ( false === $public_key ) {
				return false;
			}

			// Encrypt the API params.
			$api_params_string = http_build_query( $data );

			// Encrypt data with OAEP padding using OpenSSL.
			$encrypted_data     = '';
			$encryption_success = openssl_public_encrypt(
				$api_params_string,
				$encrypted_data,
				$public_key,
				OPENSSL_PKCS1_OAEP_PADDING  // Set padding to OAEP.
			);

			if ( ! $encryption_success ) {
				return false;
			}

			// Encode the encrypted data in Base64 to send as a string.
			$base64_encrypted_data = base64_encode( $encrypted_data );

			// Return base64 encoded encrypted data.
			return $base64_encrypted_data;
		} catch ( Exception $e ) {
			return false;
		}
	}
}
