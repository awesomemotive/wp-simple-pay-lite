<?php
/**
 * Admin: Plugin cancelled payment confirmation education
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
 * @var string $already_purchased_url The already purchased URL.
 */

if ( ! isset( $_GET['subsection'] ) || 'subscription-cancelled' !== $_GET['subsection'] ) {
	return;
}

add_filter( 'simpay_admin_page_settings_display_submit', '__return_false' );

?>
<div class="simpay-landing-zone">
	<h2 class="simpay-landing-zone__title">
		<?php
		esc_html_e(
			'Subscription Cancellation Confirmation Message',
			'stripe'
		);
		?>
	</h2>
	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'Set a personalized confirmation message to display when users initiate a subscription cancellation, ensuring clear communication and a better user experience.',
			'stripe'
		);
		?>
	</p>
	<section class="simpay-landing-zone__screenshot">
		<div class="simpay-landing-zone__screenshot-img">
			<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/subscription-cancellation-confirmation-thumb.png' ); /** @phpstan-ignore-line. */ ?>" alt="<?php echo esc_attr__( 'Subscription Cancellation Confirmation Message', 'stripe' ); ?>" />
			<a href="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/subscription-cancellation-confirmation-full.png' ); // @phpstan-ignore-line ?>" class="hover" data-lity></a>
		</div>
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
