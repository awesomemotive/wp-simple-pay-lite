<?php
/**
 * Payment Page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, WP Simple Pay, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.5.0
 *
 * @var int    $form_id Payment form ID.
 * @var string $title_desc If the title and description should show.
 * @var string $title Payment form title.
 * @var string $desc Payment form description.
 * @var string $image Image URL.
 * @var string $footer_text Footer text.
 * @var string $powered_by Powered by logo.
 * @var string $background_color Background color.
 * @var string $darker_background_color Darker background color.
 * @var string $lighter_background_color Lighter background color.
 * @var bool   $is_confirmation Determines if the payment confirmation should show.
 */

?><html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<?php wp_head(); ?>

	<style>
		html body.simpay-payment-page {
			background-color: <?php echo esc_attr( $background_color ); ?>;
		}

		.simpay-styled .simpay-form-control .simpay-btn:not(.stripe-button-el) {
			background-color: <?php echo esc_attr( $background_color ); ?>;
		}

		.simpay-styled .simpay-form-control .simpay-btn:not(.stripe-button-el):focus,
		.simpay-styled .simpay-form-control .simpay-btn:not(.stripe-button-el):hover {
			background-color: <?php echo esc_attr( $darker_background_color ); ?>;
		}

		.simpay-styled .simpay-form-control .simpay-btn:not(.stripe-button-el):focus {
			box-shadow: 0 0 0 1px #fff, 0 0 0 3px <?php echo esc_attr( $darker_background_color ); ?>;
		}
	</style>
</head>
<body class="simpay-payment-page">

	<div>
		<div class="simpay-payment-page-wrap">
			<?php if ( ! empty( $image ) ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
			<?php endif; ?>

			<?php if ( 'yes' === $title_desc ) : ?>
				<div class="simpay-embedded-heading simpay-styled simpay-heading">
					<h3 class="simpay-form-title">
						<?php echo esc_html( $title ); ?>
					</h3>
					<p class="simpay-form-description">
						<?php echo esc_html( $desc ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php
			if ( true === $is_confirmation ) :
				echo do_shortcode( '[simpay_payment_receipt]' );
			else :
				echo do_shortcode( sprintf( '[simpay id="%d"]', $form_id ) );
			endif;
			?>
		</div>

		<div class="simpay-payment-page-footer">
			<?php if ( ! empty( $footer_text ) ) : ?>
				<?php echo esc_html( $footer_text ); ?>
			<?php endif; ?>

			<?php if ( 'no' === $powered_by ) : ?>
				<a href="https://wpsimplepay.com/?utm_source=poweredby&utm_medium=link&utm_campaign=paymentpage" class="simpay-payment-page-powered-by">
					<?php esc_html_e( 'powered by', 'stripe' ); ?> <img src="<?php echo esc_url( SIMPLE_PAY_INC_URL ); // @phpstan-ignore-line ?>core/assets/images/wp-simple-pay-logo-white.svg" />
				</a>
			<?php endif; ?>
		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
