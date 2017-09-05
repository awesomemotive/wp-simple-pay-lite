<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$position = simpay_get_currency_position();
?>

	<table>
		<thead>
		<tr>
			<th colspan="2"><?php esc_html_e( 'Payment Options', 'stripe' ); ?></th>
		</tr>
		</thead>
		<tbody class="simpay-panel-section">

		<?php do_action( 'simpay_amount_options' ); ?>
		
		<tr class="simpay-panel-field <?php echo apply_filters( 'simpay_amount_options_classes', '' ); ?> toggle-_amount_type-one_time_set">
			<th>
				<label for="_amount"><?php esc_html_e( 'One-Time Amount', 'stripe' ); ?></label>
			</th>
			<td>

				<?php if ( 'left' === $position || 'left_space' === $position ) { ?>
					<span class="simpay-currency-symbol simpay-currency-symbol-left"><?php echo simpay_get_saved_currency_symbol(); ?></span>
				<?php } ?>

				<?php

				// Classes
				$classes = array(
					'simpay-field-tiny',
					'simpay-amount-input',
				);

				// Attributes
				$attr = array(
					'min' => simpay_get_stripe_minimum_amount(),
				);

				// Check saved currency and set default to 100 or 1 accordingly and set steps and class
				if ( simpay_is_zero_decimal() ) {
					$amount = simpay_get_saved_meta( $post->ID, '_amount', '100' );
				} else {
					$amount = simpay_get_saved_meta( $post->ID, '_amount', '1' );
				}

				simpay_print_field( array(
					'type'        => 'standard',
					'subtype'     => 'tel',
					'name'        => '_amount',
					'id'          => '_amount',
					'value'       => $amount,
					'attributes'  => $attr,
					'class'       => $classes,
					'placeholder' => simpay_formatted_amount( '100', simpay_get_setting( 'currency' ), false ),
				) );
				?>

				<?php if ( 'right' === $position || 'right_space' === $position ) { ?>
					<span class="simpay-currency-symbol simpay-currency-symbol-right"><?php echo simpay_get_saved_currency_symbol(); ?></span>
				<?php } ?>
			</td>
		</tr>
		</tbody>
	</table>

	<?php do_action( 'simpay_admin_after_payment_options' );
