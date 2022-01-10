<?php
/**
 * Admin: Product education dashboard widget Lite upgrade
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

$upgrade_url = simpay_pro_upgrade_url( 'dashboard-widget' );
?>

<div class="simpay-dashboard-widget-product-education">

	<h2>
		<?php
		esc_html_e(
			'You are using WP Simple Pay Lite',
			'simple-pay'
		);
		?>
	</h2>

	<p>
		<?php
		esc_html_e(
			'Upgrade to WP Simple Pay Pro to unlock on-site payment forms, custom fields, subscriptions, tax rates, and more.',
			'simple-pay'
		);
		?>
	</p>

	<p>
		<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary button-large" target="_blank" rel="noopener noreferrer">
			<?php
			esc_html_e(
				'Upgrade Now',
				'simple-pay'
			);
			?>
		</a>
	</p>

</div>
