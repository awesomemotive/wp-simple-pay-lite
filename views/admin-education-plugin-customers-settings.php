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
 * @var \SimplePay\Core\License\License $license Plugin license.
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
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Remind customers about upcoming invoices and give them an opportunity to update their payment method on file or cancel their subscription. Supports both on-site management or the %1$sStripe Customer portal%2$s.',
					'stripe'
				),
				'<a href="https://stripe.com/blog/billing-customer-portal" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
				Utils\get_external_link_markup() . '</a>'
			),
			array(
				'sup'  => array(),
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

		<?php if ( ! empty( $upgrade_subtext ) ) : ?>
		<div class="simpay-upgrade-btn-subtext">
			<?php echo esc_html( $upgrade_subtext ); ?>
		</div>
		<?php endif; ?>
	</section>

	<?php if ( false === $license->is_lite() ) : ?>
	<section>
		<small>
			<?php esc_html_e( 'Available in the Plus plan or higher', 'stripe' ); ?><br />
		</small>
	</section>
	<?php endif; ?>

</div>
