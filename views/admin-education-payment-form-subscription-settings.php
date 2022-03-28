<?php
/**
 * Admin: Payment form subscription education
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
 * @var string $activate_url The license activation URL.
 * @var string $license The plugin license key.
 */

?>

<div style="overflow: hidden">
	<div
		class="simpay-teaser-float simpay-teaser-float--inline simpay-notice"
		data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-license_subscription_upgrade' ) ); ?>"
		data-id="license_subscription_upgrade"
		data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 90 ); // @phpstan-ignore-line ?>"
	>
		<div class="simpay-teaser-float__card">

			<h2>
				<?php
				esc_html_e(
					'Need your customers to sign up for recurring payments?',
					'stripe'
				);
				?>
			</h2>

			<p>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					__(
						'%1$sUpgrade your license%2$s to accept recurring payments from your customers. You can also create installment plans, charge setup fees, and include free trials.',
						'stripe'
					),
					sprintf(
						'<a href="%s" target="_blank" rel="noopener noreferrer">',
						esc_url( $upgrade_url )
					),
					'</a>'
				),
				array(
					'a' => array(
						'href'   => true,
						'rel'    => true,
						'target' => true,
					),
				)
			);
			?>
			</p>

			<div>
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large simpay-upgrade-btn" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $upgrade_text ); ?>
				</a>

				<?php if ( ! empty( $upgrade_subtext ) ) : ?>
				<div class="simpay-upgrade-btn-subtext">
					<?php echo esc_html( $upgrade_subtext ); ?>
				</div>
				<?php endif; ?>
			</div>

			<?php if ( empty( $license_key ) ) : ?>
			<p>
				<small>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
						__(
							'Already have a license that supports subscriptions? %1$sActivate your license%2$s',
							'stripe'
						),
						sprintf(
							'<a href="%s">',
							esc_url( $activate_url )
						),
						'</a>'
					),
					array(
						'a' => array(
							'href' => true,
						),
					)
				);
				?>
				</small>
			</p>
			<?php endif; ?>

			<button type="button" class="button-link simpay-notice-dismiss">
				&times;
			</button>
		</div>
	</div>
</div>
