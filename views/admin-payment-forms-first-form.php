<?php
/**
 * Admin: Payment forms first form
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

use SimplePay\Core\Utils;

$docs_url = simpay_docs_link(
	'comprehensive guide',
	'first-payment-form',
	'no-forms-first',
	true
);

$new_url = add_query_arg(
	array(
		'post_type' => 'simple-pay',
	),
	admin_url( 'post-new.php' )
);
?>

<style>.page-title-action, #show-settings-link { display: none; }</style>

<div class="simpay-landing-zone">

	<h2 class="simpay-landing-zone__title">
		<?php esc_html_e( '🚀 Create Your First Payment Form', 'stripe' ); ?>
	</h2>

	<p class="simpay-landing-zone__subtitle">
		<strong>
			<?php
			esc_html_e(
				'It looks like you haven\'t created any payment forms yet.',
				'stripe'
			);
			?>
		</strong>
	</p>

	<p class="simpay-landing-zone__subtitle">
		<?php
		esc_html_e(
			'You can use WP Simple Pay to build payment forms with just a few clicks.',
			'stripe'
		);
		?>
	</p>

	<section class="simpay-landing-zone__empty-state-graphic">
		<img src="<?php echo esc_url( SIMPLE_PAY_INC_URL . 'core/assets/images/empty-states/no-forms.svg' ); // @phpstan-ignore-line ?>" style="width: 325px;" />
	</section>

	<section>
		<a href="<?php echo esc_url( $new_url ); ?>" class="button button-primary button-large">
			<?php esc_html_e( 'Create Your Payment Form', 'stripe' ); ?>
		</a>
	</section>

	<section>
		<span class="dashicons dashicons-editor-help"></span>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
				__(
					'Need some help? Check out our %1$scomprehensive guide%2$s',
					'stripe'
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
