<?php
/**
 * Admin: Payment forms expired license
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 *
 * @var string $renew_url URL to renew license.
 * @var string $learn_more_url "Learn More" URL.
 * @var string $action Payment form action.
 */
?>

<style>.notice { display: none; }</style>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php
		echo esc_html(
			sprintf(
				/* translators: Payment form action. "Creating" or "Editing". */
				__( '🔐 Payment Form %s is Disabled!', 'stripe' ),
				$action
			)
		);
		?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<strong>
			<?php
			esc_html_e(
				'Your WP Simple Pay Pro license has expired.',
				'stripe'
			);
			?>
		</strong>
	</p>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'Without an active license your payment forms will continue to collect payments, but you will not be able to edit them, or use other WP Simple Pay Pro functionality.',
			'stripe'
		);
		?>
	</p>

	<section class="simpay-landing-zone__empty-state-graphic">
		<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/empty-states/no-license.svg' ); // @phpstan-ignore-line ?>" style="width: 325px;" />
	</section>

	<section>
		<a href="<?php echo esc_url( $renew_url ); ?>" class="button button-primary button-large">
			<?php esc_html_e( 'Renew License', 'stripe' ); ?>
		</a>

		<br />

		<a href="<?php echo esc_url( $learn_more_url ); ?>" style="display: inline-block; margin-top: 8px;">
			<?php esc_html_e( 'Learn More', 'stripe' ); ?>
		</a>
	</section>

</div>
