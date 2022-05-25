<?php
/**
 * Admin: Payment forms missing license
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 *
 * @var string $license_url URL to activate license.
 */
?>

<style>.notice { display: none; }</style>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php esc_html_e( '🔐 WP Simple Pay Pro is Not Fully Activated!', 'stripe' ); ?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<strong>
			<?php
			esc_html_e(
				'Your WP Simple Pay Pro license key is missing.',
				'stripe'
			);
			?>
		</strong>
	</p>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'Activate WP Simple Pay Pro to start creating payment forms and collecting payments with just a few clicks, no coding required.',
			'stripe'
		);
		?>
	</p>

	<section class="simpay-landing-zone__empty-state-graphic">
		<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/empty-states/no-license.svg' ); // @phpstan-ignore-line ?>" style="width: 325px;" />
	</section>

	<section>
		<a href="<?php echo esc_url( $license_url ); ?>" class="button button-primary button-large">
			<?php esc_html_e( 'Activate WP Simple Pay Pro', 'stripe' ); ?>
		</a>
	</section>

</div>
