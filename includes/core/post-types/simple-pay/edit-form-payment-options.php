<?php
/**
 * Simple Pay: Edit form payment options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

use SimplePay\Core\PaymentForm\PriceOption;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Payment Options" Payment Form settings tab content.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_payment_options( $post_id ) {
	/**
	 * Allows output before the "Amount" field in
	 * "Payment Options" Payment Form settings tab content.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id Current Payment Form ID.
	 */
	do_action( 'simpay_amount_options', $post_id );

	/**
	 * Allows further output after the "Amount" field in
	 * "Payment Options" Payment Form settings tab content.
	 *
	 * @since 3.0
	 */
	do_action( 'simpay_admin_after_amount_options', $post_id );

	/**
	 * Allows further output after the "Payment Options" Payment Form
	 * settings tab content.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id Current Payment Form ID.
	 */
	do_action( 'simpay_admin_after_payment_options' );
}
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\add_payment_options' );

/**
 * Adds "Custom Amount" setting to "Payment Options" Payment Form
 * settings tab.
 *
 * @since 3.8.0
 * @since 4.1.0 Deprecated.
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_custom_amount_options( $post_id ) {
	_doing_it_wrong(
		__FUNCTION__,
		esc_html__( 'No longer used.', 'stripe' ),
		'4.1.0'
	);
}

/**
 * Adds "Payment Mode" setting to toggle between live and test mode
 * on a per-form basis.
 *
 * @since 3.9.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_payment_mode( $post_id ) {
	?>

<table>
	<tr class="simpay-panel-field">
		<th>
			<label for="_livemode"><?php esc_html_e( 'Payment Mode', 'stripe' ); ?></label>
		</th>
		<td>
			<?php
			$livemode = simpay_get_saved_meta( $post_id, '_livemode', '' );

			$all_options = array(
				''  => esc_html(
					sprintf(
						__( 'Global Setting (%s)', 'stripe' ),
						simpay_is_test_mode()
							? __( 'Test Mode', 'stripe' )
							: __( 'Live Mode', 'stripe' )
					)
				),
				'0' => esc_html__( 'Test Mode', 'stripe' ),
				'1' => esc_html__( 'Live Mode', 'stripe' ),
			);

			$available_options = $all_options;

			if ( empty( simpay_get_setting( 'test_secret_key', '' ) ) ) {
				unset( $available_options['0'] );
			}

			if ( empty( simpay_get_setting( 'live_secret_key', '' ) ) ) {
				unset( $available_options['1'] );
			}

			$class = array( 'simpay-payment-modes' );

			foreach ( $available_options as $mode => $label ) {
				$class[] = 'simpay-payment-mode--' . $mode;
			}

			simpay_print_field(
				array(
					'type'    => 'radio',
					'name'    => '_livemode',
					'id'      => '_livemode',
					'value'   => $livemode,
					'options' => $all_options,
					'inline'  => 'inline',
					'class'   => $class,
				)
			);

			$keys_url = add_query_arg(
				array(
					'post_type' => 'simple-pay',
					'page'      => 'simpay_settings',
					'tab'       => 'keys',
				),
				admin_url( 'edit.php' )
			);
			?>

			<?php if ( count( $available_options ) < 3 ) : ?>
			<p class="description" style="margin: 8px 0 15px;">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'Connect to Stripe in both %1$spayment modes globally%2$s to use on a per-form basis.', 'stripe' ),
						'<a href="' . esc_url( $keys_url ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'a' => array(
							'href'   => true,
							'target' => '_blank',
							'rel'    => 'noopener noreferrer',
						),
					)
				);
				?>
			</p>
			<?php endif; ?>

			<p class="description">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'While in Test Mode no live payments are processed. Make sure Test mode is enabled in your %1$sStripe dashboard%2$s to view your test transactions.', 'stripe' ),
						'<a href="https://dashboard.stripe.com/test/dashboard" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'a' => array(
							'href'   => true,
							'target' => '_blank',
							'rel'    => 'noopener noreferrer',
						),
					)
				);
				?>
			</p>
		</td>
	</tr>
</table>

	<?php
}
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\add_payment_mode', 5 );

/**
 * Adds "One-Time Amount" amount input.
 *
 * @since 4.1.0
 * @access private
 *
 * @param int $post_id Current Payment Form ID.
 */
function __unstable_add_payment_amount( $post_id ) {
	$form = simpay_get_form( $post_id );

	if ( false === $form ) {
		return;
	}

	$currency_position = simpay_get_currency_position();
	?>

	<table>
		<tbody class="simpay-panel-section">
			<tr class="simpay-panel-field">
				<th>
					<label for="_amount">
						<?php esc_html_e( 'One-Time Amount', 'stripe' ); ?>
					</label>
				</th>
				<td>
					<div class="simpay-currency-field">
						<?php if ( in_array( $currency_position, array( 'left', 'left_space' ), true ) ) : ?>
							<div
								class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
								style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
							>
								<?php echo simpay_get_saved_currency_symbol(); ?>
							</div>
						<?php endif; ?>

						<?php
						$prices = simpay_get_payment_form_prices( $form );

						if ( ! empty( $prices ) ) {
							$price = current( $prices );

							$currency = $price->currency;
						} else {
							$currency = strtolower(
								simpay_get_setting( 'currency', 'USD' )
							);

							$price = new PriceOption(
								array(
									'unit_amount' => simpay_get_currency_minimum(
										$currency
									),
									'currency'    => $currency,
									'default'     => true,
									'can_recur'   => false,
								),
								$form
							);
						}

						$classes = array(
							'simpay-field',
							'simpay-field-tiny',
							'simpay-field-amount',
							'simpay-price-amount',
						);

						$amount = simpay_format_currency(
							$price->unit_amount,
							$price->currency,
							false
						);

						$placeholder = simpay_format_currency(
							simpay_get_currency_minimum( $currency ),
							$currency,
							false
						);

						simpay_print_field(
							array(
								'type'        => 'standard',
								'subtype'     => 'tel',
								'name'        => '_simpay_prices[0][unit_amount]',
								'id'          => '_amount',
								'value'       => $amount,
								'class'       => $classes,
								'placeholder' => $placeholder,
							)
						);

						simpay_print_field(
							array(
								'type'        => 'standard',
								'subtype'     => 'hidden',
								'name'        => '_simpay_prices[0][currency]',
								'value'       => strtolower(
									simpay_get_setting( 'currency', 'USD' )
								),
							)
						);

						simpay_print_field(
							array(
								'type'    => 'standard',
								'subtype' => 'hidden',
								'name'    => '_simpay_prices[0][unit_amount_current]',
								'value'   => $amount,
							)
						);

						simpay_print_field(
							array(
								'type'    => 'standard',
								'subtype' => 'hidden',
								'name'    => '_simpay_prices[0][id]',
								'value'   => 'simpay_price_stub',
							)
						);

						simpay_print_field(
							array(
								'type'    => 'standard',
								'subtype' => 'hidden',
								'name'    => '_simpay_prices[0][id_current]',
								'value'   => $price->id,
							)
						);

						simpay_print_field(
							array(
								'type'    => 'standard',
								'subtype' => 'hidden',
								'name'    => '_simpay_prices[0][amount_type]',
								'value'   => 'one-time',
							)
						);
						?>

						<?php if ( in_array( $currency_position, array( 'right', 'right_space' ), true ) ) : ?>
							<div
								class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-right"
								style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
							>
								<?php echo simpay_get_saved_currency_symbol(); ?>
							</span>
						<?php endif; ?>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_add_payment_amount'
);
