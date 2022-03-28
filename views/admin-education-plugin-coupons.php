<?php
/**
 * Admin: Plugin coupon education
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

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Coupons', 'stripe' ); ?>
	</h1>
	<hr class="wp-header-end">

	<div class="simpay-landing-zone">

		<h2 class="simpay-landing-zone__title">
			<?php echo esc_html_e( '🏷 Offer Coupon Codes to Customers', 'stripe' ); ?>
		</h2>

		<p class="simpay-landing-zone__subtitle">
			<?php
			esc_html_e(
				'Allow fixed amount or percentage discounts to one-time or recurring payments with WP Simple Pay Pro. Limit coupon code redemptions by count, date, or specific payment forms.',
				'stripe'
			);
			?>
		</p>

		<section class="simpay-landing-zone__screenshot">
			<div class="simpay-landing-zone__screenshot-img">
				<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/coupons-thumb.png' ); // @phpstan-ignore-line ?>" alt="<?php echo esc_attr( 'Add new coupon', 'simple-pay' ); ?>" />
				<a href="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/coupons-full.png' ); // @phpstan-ignore-line ?>" class="hover" data-lity></a>
			</div>

			<ul>
				<li>
					<?php esc_html_e( 'Fixed amount or percentage discounts', 'stripe' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Restrict coupons to specific payment forms', 'stripe' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Apply to a specific number of invoices', 'stripe' ); ?>
				</li>
				<li>
					<?php esc_html_e( 'Add unlimited coupon codes', 'stripe' ); ?>
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

</div>
