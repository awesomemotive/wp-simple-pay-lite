<?php
/**
 * Admin: Product education (Personal) license settings upgrade promo
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 *
 * @var string $upgrade_url The upgrade URL.
 * @var string $upgrade_text The upgrade button text.
 * @var string $upgrade_subtext The upgrade subtext (used in the header in this view).
 * @var string $license_url The URL to force refresh the license (reload the page).
 */

?>

<div
	id="simpay-license-upgrade"
	class="simpay-settings-upgrade simpay-card simpay-notice"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'simpay-dismiss-notice-simpay-settings-pro-license-upgrade' ) ); ?>"
	data-id="simpay-settings-pro-license-upgrade"
	data-lifespan="<?php echo esc_attr( DAY_IN_SECONDS * 30 ); // @phpstan-ignore-line ?>"
>
	<div class="simpay-settings-upgrade__inner">
		<?php if ( ! empty( $upgrade_subtext ) ) : ?>
		<h4>
			<?php echo esc_html( $upgrade_subtext ); ?>
		</h4>
		<?php endif; ?>

		<h3>
			<?php
			esc_html_e(
				'Upgrade Your License Today and Save',
				'stripe'
			);
			?>
		</h3>

		<ul>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Accept Recurring Payments', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Create Installment Plans', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Charge Initial Setup Fees', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Offer Free Trials', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'One-Time & Recurring Toggle', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'User-Managed Subscriptions', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Send Renewal Reminders', 'stripe' ); ?>
			</li>
			<li>
				<div class="dashicons dashicons-yes"></div>
				<?php esc_html_e( 'Plus much more...', 'stripe' ); ?>
			</li>
		</ul>

		<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large simpay-upgrade-btn simpay-upgrade-btn-large" target="_blank" rel="noopener noreferrer">
			<?php echo esc_html( $upgrade_text ); ?>
		</a>
	</div>

	<button type="button" class="button-link simpay-notice-dismiss">
		&times;
	</button>
</div>
