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

		<?php if ( ! empty( $upgrade_subtext ) ) : ?>
		<div class="simpay-upgrade-btn-subtext">
			<?php echo esc_html( $upgrade_subtext ); ?>
		</div>
		<?php endif; ?>
	</section>

</div>
