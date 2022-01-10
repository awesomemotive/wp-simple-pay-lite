<?php
/**
 * Admin: Payment forms Stripe Connect
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

use SimplePay\Core\Utils;

$docs_url = simpay_docs_link(
	'',
	'stripe-setup',
	'global-settings',
	true
);
?>

<style>.page-title-action, #show-settings-link, .search-box { display: none; }</style>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php esc_html_e( '👋 You\'re Almost Ready!', 'simple-pay' ); ?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<strong>
			<?php
			esc_html_e(
				'You need to connect your Stripe account before creating a payment form.',
				'simple-pay'
			);
			?>
		</strong>
	</p>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'WP Simple Pay requires Stripe Connect for the highest reliability and security. Connect now to start accepting payments instantly.',
			'simple-pay'
		);
		?>
	</p>

	<section>
		<a href="<?php echo esc_url( simpay_get_stripe_connect_url() ); ?>" class="wpsp-stripe-connect">
			<span>
				<?php esc_html_e( 'Connect with Stripe', 'simple-pay' ); ?>
			</span>
		</a>
	</section>

	<section>
		<span class="dashicons dashicons-editor-help"></span>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Have questions about connecting with Stripe? %1$sView the Stripe Connect documentation%2$s',
					'simple-pay'
				),
				'<a href="' . esc_url( $docs_url ) . '" target="_blank" rel="noopener noreferrer" class="simpay-external-link">',
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
					'class' => true,
				),
			)
		);
		?>
	</section>

</div>
