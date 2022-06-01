<?php
/**
 * Admin: Plugin email settings education
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string $title The title of the page.
 * @var string $upgrade_url The upgrade URL.
 * @var string $upgrade_text The upgrade button text.
 * @var string $upgrade_subtext The upgrade button subtext.
 * @var string $already_purchased_url The already purchased URL.
 */

?>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php echo esc_html( $title ); ?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'Send white-label emails with WP Simple Pay Pro. Gain complete control over emails with payment-specific information, custom content, and enhanced delivery options.',
			'stripe'
		);
		?>
	</p>

	<section class="simpay-landing-zone__screenshot">
		<div class="simpay-landing-zone__screenshot-img">
			<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/email-thumb.png' ); // @phpstan-ignore-line ?>" alt="<?php echo esc_attr( 'Payment Receipt settings', 'simple-pay' ); ?>" />
			<a href="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/email-full.png' ); // @phpstan-ignore-line ?>" class="hover" data-lity></a>
		</div>

		<ul>
			<li>
				<?php esc_html_e( 'Dynamic email merge tags', 'stripe' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Completely customizable content', 'stripe' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Enhanced delivery options', 'stripe' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'White-label (no Stripe branding)', 'stripe' ); ?>
			</li>
		</ul>
	</section>

	<section>
		<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large simpay-upgrade-btn" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $upgrade_text ); ?>
		</a>

		<div style="margin-top: 15px;">
			<a href="<?php echo esc_url( $already_purchased_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Already purchased?', 'stripe' ); ?>
			</a>
		</div>

		<?php if ( ! empty( $upgrade_subtext ) ) : ?>
		<div class="simpay-upgrade-btn-subtext">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" role="img" aria-hidden="true" focusable="false">
				<path d="M16.7 7.1l-6.3 8.5-3.3-2.5-.9 1.2 4.5 3.4L17.9 8z"></path>
			</svg>

			<?php echo $upgrade_subtext; ?>
		</div>
		<?php endif; ?>
	</section>

</div>
