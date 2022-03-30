<?php
/**
 * Admin: Payment form payment education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string $upgrade_url The upgrade URL.
 * @var string $upgrade_text The upgrade button text.
 * @var string $upgrade_subtext The upgrade button subtext.
 */

?>

<div style="overflow: hidden">
	<div
		class="simpay-teaser-float simpay-teaser-float--inline simpay-notice"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-form-settings-payment-license-upgrade' ) ); ?>"
		data-id="simpay-form-settings-payment-license-upgrade"
		data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 90 ); // @phpstan-ignore-line ?>"
	>
		<div class="simpay-teaser-float__card">
			<h2>
				<?php esc_html_e( 'Multiple Price Options & Subscriptions', 'stripe' ); ?>
			</h2>

			<p>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening <strong> tag, do not translate. %2$s Closing </strong> tag, do not translate. */
						__(
							'Allow customers to choose between multiple price options, currencies, and more. Accept %1$srecurring payments%2$s, create installment plans, charge setup fees, and include free trials.',
							'stripe'
						),
						'<strong>',
						'</strong>'
					),
					array(
						'strong' => array(),
					)
				);
				?>
			</p>

			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large simpay-upgrade-btn" target="_blank" rel="noopener noreferrer">
				<?php echo esc_html( $upgrade_text ); ?>
			</a>

			<?php if ( ! empty( $upgrade_subtext ) ) : ?>
			<div class="simpay-upgrade-btn-subtext">
				<?php echo esc_html( $upgrade_subtext ); ?>
			</div>
			<?php endif; ?>

			<button type="button" class="button-link simpay-notice-dismiss">
				&times;
			</button>
		</div>
	</div>
</div>
