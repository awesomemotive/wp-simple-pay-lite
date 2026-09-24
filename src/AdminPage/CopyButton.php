<?php
/**
 * Admin: Copy-to-clipboard button
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\AdminPage;

/**
 * CopyButton class.
 *
 * Prints the small copy-to-clipboard button used on the Transaction and
 * Subscription detail views. Behavior lives in simpay-admin-transactions.js.
 *
 * @since 4.17.4
 */
class CopyButton {

	/**
	 * Prints a copy button for a value. Prints nothing for an empty value.
	 *
	 * @since 4.17.4
	 *
	 * @param string|int|null $text Value to copy.
	 * @return void
	 */
	public static function render( $text ) {
		$text = (string) $text;

		if ( '' === $text ) {
			return;
		}
		?>
		<button
			type="button"
			class="simpay-txn-copy"
			data-clipboard-text="<?php echo esc_attr( $text ); ?>"
			data-copied-label="<?php esc_attr_e( 'Copied!', 'stripe' ); ?>"
			aria-label="<?php esc_attr_e( 'Copy to clipboard', 'stripe' ); ?>"
			title="<?php esc_attr_e( 'Copy to clipboard', 'stripe' ); ?>"
		>
			<span class="dashicons dashicons-admin-page" aria-hidden="true"></span>
		</button>
		<?php
	}
}
