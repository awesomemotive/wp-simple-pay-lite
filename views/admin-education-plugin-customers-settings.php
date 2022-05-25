<?php
/**
 * Admin: Plugin subscription management settings education
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

use SimplePay\Core\Utils;

?>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php
		esc_html_e(
			'🙋‍♀️ Allow Customers to Manage Subscriptions',
			'stripe'
		);
		?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'Remind customers about upcoming invoices and give them an opportunity to update their payment method on file or cancel their subscription. Supports both on-site management or the Stripe Customer portal.',
			'stripe'
		);
		?>
	</p>

	<section class="simpay-landing-zone__screenshot">
		<div class="simpay-landing-zone__screenshot-img">
			<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/customers-thumb.png' ); // @phpstan-ignore-line ?>" alt="<?php echo esc_attr( 'Payment Receipt settings', 'simple-pay' ); ?>" />
			<a href="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/settings/customers-full.png' ); // @phpstan-ignore-line ?>" class="hover" data-lity></a>
		</div>

		<ul>
			<li>
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
						__(
							'%1$sStripe Customer portal%2$s support',
							'stripe'
						),
						'<a href="https://stripe.com/blog/billing-customer-portal" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
						Utils\get_external_link_markup() . '</a>'
					),
					array(
						'a'    => array(
							'href'   => true,
							'class'  => true,
							'target' => true,
							'rel'    => true,
						),
						'span' => array(
							'class' => 'screen-reader-text',
						),
					)
				);
				?>
			</li>
			<li>
				<?php esc_html_e( 'Cancel immediately or at end of cycle', 'stripe' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Upcoming invoice reminders', 'stripe' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Upgrade or downgrade subscriptions', 'stripe' ); ?>
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
