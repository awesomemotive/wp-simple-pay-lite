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
					'simpay-minimum-amount-required',
				);

				// Check saved currency and set default to 100 or 1 accordingly and set steps and class.
				$amount = simpay_get_saved_meta( $post->ID, '_amount', simpay_global_minimum_amount() );

				simpay_print_field( array(
					'type'        => 'standard',
					'subtype'     => 'tel',
					'name'        => '_amount',
					'id'          => '_amount',
					'value'       => $amount,
					'class'       => $classes,
					'placeholder' => simpay_format_currency( simpay_global_minimum_amount(), simpay_get_setting( 'currency' ), false ),
				) );

				?>

				<?php if ( 'right' === $position || 'right_space' === $position ) { ?>
					<span class="simpay-currency-symbol simpay-currency-symbol-right"><?php echo simpay_get_saved_currency_symbol(); ?></span>
				<?php } ?>
			</td>
		</tr>
		</tbody>
	</table>

	<?php do_action( 'simpay_admin_after_amount_options' ); ?>

	<table>
		<tbody class="simpay-panel-section">

		<tr class="simpay-panel-field">
			<th>
				<label for="_success_redirect_type"><?php esc_html_e( 'Payment Success Page', 'stripe' ); ?></label>
			</th>
			<td>
				<?php

				$success_redirect_type = simpay_get_saved_meta( $post->ID, '_success_redirect_type', 'default' );

				simpay_print_field( array(
					'type'        => 'radio',
					'name'        => '_success_redirect_type',
					'id'          => '_success_redirect_type',
					'class'       => array( 'simpay-multi-toggle' ),
					'options'     => array(
						'default'  => __( 'Global Setting', 'stripe' ),
						'page'     => __( 'Specific Page', 'stripe' ),
						'redirect' => __( 'Redirect URL', 'stripe' ),
					),
					'inline'      => 'inline',
					'default'     => 'default',
					'value'       => $success_redirect_type,
					// Description set below
				) );
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field toggle-_success_redirect_type-default <?php echo 'default' !== $success_redirect_type ? 'simpay-panel-hidden' : ''; ?>">
			<th></th>
			<td>
				<p class="description">
					<?php _e( 'By default, the payment success page indicated in Simple Pay > Settings > General will be used. This option allows you to specify an alternate page or URL for this payment form only.', 'stripe' ); ?>
				</p>
			</td>
		</tr>

		<tr class="simpay-panel-field toggle-_success_redirect_type-page <?php echo 'page' !== $success_redirect_type ? 'simpay-panel-hidden' : ''; ?>">
			<th>&nbsp;</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'        => 'select',
					'page_select' => 'page_select',
					'name'        => '_success_redirect_page',
					'id'          => '_success_redirect_page',
					'value'       => simpay_get_saved_meta( $post->ID, '_success_redirect_page', '' ),
					'description' => __( 'Choose a page from your site to redirect to after a successful transaction.', 'stripe' ),
				) );

				?>
			</td>
		</tr>

		<tr class="simpay-panel-field toggle-_success_redirect_type-redirect <?php echo 'redirect' !== $success_redirect_type ? 'simpay-panel-hidden' : ''; ?>">
			<th>&nbsp;</th>
			<td>
				<?php

				simpay_print_field( array(
					'type'        => 'standard',
					'subtype'     => 'text',
					'name'        => '_success_redirect_url',
					'id'          => '_success_redirect_url',
					'class'       => array(
						'simpay-field-text',
					),
					'value'       => simpay_get_saved_meta( $post->ID, '_success_redirect_url', '' ),
					'description' => __( 'Enter a custom redirect URL for successful transactions.', 'stripe' ),
				) );

				?>
			</td>
		</tr>

		</tbody>
	</table>

<?php do_action( 'simpay_admin_after_payment_options' );
