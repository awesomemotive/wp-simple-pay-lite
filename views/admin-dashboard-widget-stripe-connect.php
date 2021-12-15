<?php
/**
 * Admin: Product education dashboard widget Stripe connect
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2021, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

$docs_url = simpay_docs_link(
	'',
	'stripe-setup',
	'global-settings',
	true
);
?>

<div class="simpay-dashboard-widget-product-education">

	<h2>
		<?php
		esc_html_e(
			'Connect with Stripe to use WP Simple Pay',
			'simple-pay'
		);
		?>
	</h2>

	<p>
		<?php
		esc_html_e(
			'Stripe Connect is the most secure, safe and reliable way to integrate Stripe with your website. Connect now to start accepting payments with WP Simple Pay.',
			'simple-pay'
		);
		?>
	</p>

	<p>
		<a href="<?php echo esc_url( simpay_get_stripe_connect_url() ); ?>" class="wpsp-stripe-connect">
			<span>
				<?php esc_html_e( 'Connect with Stripe', 'simple-pay' ); ?>
			</span>
		</a>

		<a href="<?php echo esc_url( $docs_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer" style="margin-left: 5px;">
			<?php
			esc_html_e(
				'Learn More',
				'simple-pay'
			);
			?>
		</a>
	</p>

</div>
