<?php
/**
 * Admin: Product education dashboard widget first form
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

$docs_url = simpay_docs_link(
	'Learn more',
	'first-payment-form',
	'dashboard-widget-first-form',
	true
);

$new_url = add_query_arg(
	array(
		'post_type' => 'simple-pay',
	),
	admin_url( 'post-new.php' )
);
?>

<div class="simpay-dashboard-widget-product-education">

	<h2>
		<?php
		esc_html_e(
			'Create Your First Payment Form to Start Collecting Payments',
			'stripe'
		);
		?>
	</h2>

	<p>
		<?php
		esc_html_e(
			'You can use WP Simple Pay to easily collect payments with just a few clicks.',
			'stripe'
		);
		?>
	</p>

	<p>
		<a href="<?php echo esc_url( $new_url ); ?>" class="button button-primary">
			<?php
			esc_html_e(
				'Create Your Payment Form',
				'stripe'
			);
			?>
		</a>

		<a href="<?php echo esc_url( $docs_url ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
			<?php
			esc_html_e(
				'Learn More',
				'stripe'
			);
			?>
		</a>
	</p>

</div>
