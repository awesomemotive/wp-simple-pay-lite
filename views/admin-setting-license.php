<?php
/**
 * Admin setting: License
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 *
 * @var bool                            $has_config If the license key is set via wp-config.php
 * @var \SimplePay\Core\License\License $license Plugin license.
 * @var string                          $feedback License key feedback.
 * @var string                          $refresh_url URL to refresh/reactivate license key.
 * @var string                          $nonce License management nonce.
 */

use SimplePay\Core\Utils;

?>

<p>
	<?php
	esc_html_e(
		'A valid license key is required to enable automatic updates and fully activate WP Simple Pay Pro.',
		'stripe'
	);
	?>
</p>

<p>
<?php
	echo wp_kses_post(
		sprintf(
			/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
			__( 'Retrieve your license key from %1$syour WP Simple Pay account%2$s or purchase receipt email.', 'stripe' ),
			sprintf(
				'<a href="%s" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
				simpay_ga_url( 'https://wpsimplepay.com/my-account/licenses/', 'license-settings', 'activate your website' )
			),
			Utils\get_external_link_markup() . '</a>'
		)
	);
?>
</p>

<div class="simpay-license-field-wrapper">

	<?php if ( false === $has_config ) : ?>
		<div class="simpay-license-field">
			<input
				type="password"
				id="simpay-settings-license-key-license-key"
				name="simpay-license-key"
				value="<?php echo esc_attr( (string) $license->get_key() ); ?>"
				class="regular-text"
				style="line-height: 1; font-size: 1.15rem; padding: 10px;"
			/>

			<?php if ( 'valid' !== $license->get_status() ) : ?>
				<button
					class="button button-primary simpay-license-button simpay-license-button--activate"
					id="simpay-activate-license"
				>
					<?php esc_html_e( 'Activate', 'stripe' ); ?>
				</button>
				<input type="hidden" name="simpay-action" value="simpay-activate-license" />
			<?php else : ?>
				<button
					class="button button-secondary simpay-license-button simpay-license-button--deactivate"
					id="simpay-deactivate-license"
				>
					<?php esc_html_e( 'Deactivate', 'stripe' ); ?>
				</button>
				<input type="hidden" name="simpay-action" value="simpay-deactivate-license" />
			<?php endif; ?>

			<input type="hidden" name="simpay-license-nonce" value="<?php echo esc_attr( $nonce ); ?>" />
		</div>

		<?php if ( ! empty( $feedback ) ) : ?>
		<div class="simpay-license-message simpay-license-message--<?php echo esc_html( $license->is_valid() ? 'valid' : 'invalid' ); ?>">
			<?php echo wp_kses_post( $feedback ); ?>
			<a href="<?php echo esc_url( $refresh_url ); ?>">
				<?php esc_html_e( 'Refresh Key', 'stripe' ); ?>
			</a>
		</div>
		<?php endif; ?>
	<?php else : ?>
		<p>
			<?php
				echo wp_kses(
					__( 'Your license key is globally defined via <code>SIMPLE_PAY_LICENSE_KEY</code> set in <code>wp-config.php</code>. It cannot be modified from this screen.', 'stripe' ),
					array(
						'code' => array(),
					)
				);
			?>

			<a href="<?php echo esc_url( $refresh_url ); ?>">
				<?php esc_html_e( 'Refresh license data.', 'stripe' ); ?>
			</a>
		</p>
	<?php endif; ?>

</div>
