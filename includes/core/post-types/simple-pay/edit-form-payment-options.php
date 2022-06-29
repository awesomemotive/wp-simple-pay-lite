<?php
/**
 * Simple Pay: Edit form payment options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
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
						/* translators: %s Payment mode setting. */
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

			<p class="description" style="margin-top: 12px;">
				<?php
				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag to Stripe Dashboard, do not translate. %2$s Closing anchor tag, do not translate. */
						__( 'While in Test Mode no live payments are processed. Make sure "Test mode" is enabled in your %1$sStripe dashboard%2$s to view your test transactions.', 'stripe' ),
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
add_action( 'simpay_form_settings_meta_payment_options_panel', __NAMESPACE__ . '\\add_payment_mode', 3 );

/**
 * Outputs markup for the price option list.
 *
 * @since 4.4.7
 *
 * @param int $post_id Current Post ID (Payment Form ID).
 * @return void
 */
function __unstable_add_price_options( $post_id ) {
	$form = simpay_get_form( $post_id );

	if ( false === $form ) {
		return;
	}

	$prices = simpay_get_payment_form_prices( $form );

	// @todo cleanup, remove duplication in Pro.
	if ( empty( $prices ) ) {
		$template = __unstable_simpay_get_payment_form_template_from_url();

		// Generate from a template.
		if ( ! empty( $template ) ) {
			foreach ( $template['data']['prices'] as $price ) {
				$price                     = new PriceOption( $price, $form );
				$price->__unstable_unsaved = true;

				$prices[ wp_generate_uuid4() ] = $price;
			}

			// Single price option fallback.
		} else {
			$currency = strtolower(
				simpay_get_setting( 'currency', 'USD' )
			);

			$prices = array(
				wp_generate_uuid4() => new PriceOption(
					array(
						'unit_amount' => simpay_get_currency_minimum( $currency ),
						'currency'    => $currency,
					),
					$form
				),
			);
		}

		$price    = current( $prices );
		$currency = $price->currency;

		$amount = simpay_format_currency(
			$price->unit_amount,
			$price->currency,
			false
		);

		$current_amount = '0.00';
	} else {
		$price    = current( $prices );
		$currency = $price->currency;

		$amount = simpay_format_currency(
			$price->unit_amount,
			$price->currency,
			false
		);

		$current_amount = $amount;
	}

	$currency_position = simpay_get_currency_position();

	simpay_print_field(
		array(
			'type'    => 'standard',
			'subtype' => 'hidden',
			'name'    => '_simpay_prices[0][currency]',
			'value'   => strtolower(
				simpay_get_setting( 'currency', 'USD' )
			),
		)
	);

	simpay_print_field(
		array(
			'type'    => 'standard',
			'subtype' => 'hidden',
			'name'    => '_simpay_prices[0][unit_amount_current]',
			'value'   => $current_amount,
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

	$upgrade_add_price_title = esc_html__(
		'Unlock Multiple Price Options',
		'stripe'
	);

	$upgrade_add_price_description = esc_html__(
		'We\'re sorry, adding more than one price option is not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_add_price_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Add Price'
	);

	$upgrade_add_price_purchased_url = simpay_docs_link(
		'Add Price (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		'form-payment-method-settings',
		true
	);

	$upgrade_amount_type_title = esc_html__(
		'Unlock Subscription Functionality',
		'stripe'
	);

	$upgrade_amount_type_description = esc_html__(
		'We\'re sorry, recurring payments through subscriptions are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_amount_type_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Subscriptions'
	);

	$upgrade_amount_type_purchased_url = simpay_docs_link(
		'Subscriptions Price (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		'form-payment-method-settings',
		true
	);

	$upgrade_custom_title = esc_html__(
		'Unlock Custom Amounts',
		'stripe'
	);

	$upgrade_custom_description = esc_html__(
		'We\'re sorry, custom amounts are not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
		'stripe'
	);

	$upgrade_custom_url = simpay_pro_upgrade_url(
		'form-price-option-settings',
		'Custom amount'
	);

	$upgrade_custom_purchased_url = simpay_docs_link(
		'Custom amount (already purchased)',
		'upgrading-wp-simple-pay-lite-to-pro',
		'form-payment-method-settings',
		true
	);
	?>

	<table>
		<tr class="simpay-panel-field">
			<th>
				<strong>
					<?php esc_html_e( 'Price Options', 'stripe' ); ?>
				</strong>
			</th>
			<td style="border-bottom: 0; padding-bottom: 10px;">
				<div style="margin-bottom: 15px;">
					<button
						id="simpay-add-price-lite"
						class="button button-secondary"
						data-available="no"
						data-upgrade-title="<?php echo esc_attr( $upgrade_add_price_title ); ?>"
						data-upgrade-description="<?php echo esc_attr( $upgrade_add_price_description ); ?>"
						data-upgrade-url="<?php echo esc_url( $upgrade_add_price_url ); ?>"
						data-upgrade-purchased-url="<?php echo esc_url( $upgrade_add_price_purchased_url ); ?>"
					>
						<?php esc_html_e( 'Add Price', 'stripe' ); ?>
					</button>
				</div>

				<div class="simpay-prices-wrap postbox" aria-expanded="false" style="margin-bottom: 0; border-radius: 4px;">
					<h2 class="simpay-hndle" style="padding: 10px 12px;">
						<span class="custom-field-dashicon dashicons dashicons-menu-alt2" style="cursor: move;"></span>

						<strong class="simpay-price-label-display">
							<?php esc_html_e( 'Default Price', 'stripe' ); ?>
						</strong>
					</h2>
					<div class="simpay-field-data simpay-metabox-content inside" style="border-radius: 4px; border-bottom-color: #c3c4c7;">
						<table>
							<tbody>
								<tr class="simpay-panel-field">
									<th>
										<label
											for="simpay-price-unit_amount-4d96ab8b-4bed-4f1d-9f6b-7991ca590fcd"
											class="screen-reader-text"
										>
											<?php
											esc_html_e(
												'Amount',
												'stripe'
											);
											?>
										</label>
									</th>
									<td style="border-bottom: 0;">
										<div style="display: flex; margin-bottom: 15px;">
											<div style="display: flex; align-items: center;">
												<?php if ( in_array( $currency_position, array( 'left', 'left_space' ), true ) ) : ?>
													<div
														class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-left"
														style="border-top-right-radius: 0; border-bottom-right-radius: 0;"
													>
														<?php echo simpay_get_saved_currency_symbol(); ?>
													</div>
												<?php endif; ?>

												<?php
												$classes = array(
													'simpay-field',
													'simpay-field-tiny',
													'simpay-field-amount',
													'simpay-price-amount',
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
												?>
												<?php if ( in_array( $currency_position, array( 'right', 'right_space' ), true ) ) : ?>
													<div
														class="simpay-price-currency-symbol simpay-currency-symbol simpay-currency-symbol-right"
														style="border-top-left-radius: 0; border-bottom-left-radius: 0;"
													>
														<?php echo simpay_get_saved_currency_symbol(); ?>
													</div>
												<?php endif; ?>
											</div>

											<fieldset style="margin-left: 15px;">
												<legend class="screen-reader-text">
													<?php esc_html_e( 'Amount Type', 'stripe' ); ?>
												</legend>

												<div class="button-group simpay-price-amount-type">
													<button
														class="button button-primary"
														aria-title="<?php esc_attr_e( 'One time', 'stripe' ); ?>"
														data-amount-type="one-time"
														onClick="return false;"
													>
														<?php esc_html_e( 'One time', 'stripe' ); ?>
													</button>

													<button
														class="button"
														aria-title="<?php esc_attr_e( 'Subscription', 'stripe' ); ?>"
														id="simpay-amount-type-lite"
														data-available="no"
														data-upgrade-title="<?php echo esc_attr( $upgrade_amount_type_title ); ?>"
														data-upgrade-description="<?php echo esc_attr( $upgrade_amount_type_description ); ?>"
														data-upgrade-url="<?php echo esc_url( $upgrade_amount_type_url ); ?>"
														data-upgrade-purchased-url="<?php echo esc_url( $upgrade_amount_type_purchased_url ); ?>"
													>
														<?php esc_html_e( 'Subscription', 'stripe' ); ?>
													</button>
												</div>
											</fieldset>
										</div>

										<div style="margin-bottom: 10px;">
											<label for="simpay-can-recur-lite">
												<input
													type="checkbox"
													id="simpay-can-recur-lite"
													data-available="no"
													data-upgrade-title="<?php echo esc_attr( $upgrade_amount_type_title ); ?>"
													data-upgrade-description="<?php echo esc_attr( $upgrade_amount_type_description ); ?>"
													data-upgrade-url="<?php echo esc_url( $upgrade_amount_type_url ); ?>"
													data-upgrade-purchased-url="<?php echo esc_url( $upgrade_amount_type_purchased_url ); ?>"
												/>
												<?php
												esc_html_e(
													'Allow price to optionally be purchased as a subscription',
													'stripe'
												);
												?>
											</label>
										</div>

										<div>
											<label for="simpay-custom-lite">
												<input
													type="checkbox"
													id="simpay-custom-lite"
													data-available="no"
													data-upgrade-title="<?php echo esc_attr( $upgrade_custom_title ); ?>"
													data-upgrade-description="<?php echo esc_attr( $upgrade_custom_description ); ?>"
													data-upgrade-url="<?php echo esc_url( $upgrade_custom_url ); ?>"
													data-upgrade-purchased-url="<?php echo esc_url( $upgrade_custom_purchased_url ); ?>"
												/>
												<?php
												esc_html_e(
													'Allow amount to be determined by user',
													'stripe'
												);
												?>
											</label>
										</div>
									</td>
								</tr>
							</table>
						</div>
					</div>
				</div>
			</td>
		</tr>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_add_price_options'
);

/**
 * Outputs markup for the payment method list.
 *
 * @since 4.4.7
 *
 * @return void
 */
function __unstable_add_payment_methods() {
	$payment_methods = array(
		'card'       => array(
			'name'       => __( 'Card', 'stripe' ),
			'icon'       => '<svg height="30" width="30" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"></path><path d="M26 11H6v-.938C6 9.2 6.56 8.5 7.25 8.5h17.5c.69 0 1.25.7 1.25 1.563zm0 3.125v8.125c0 .69-.56 1.25-1.25 1.25H7.25c-.69 0-1.25-.56-1.25-1.25v-8.125zM11 18.5a1.25 1.25 0 0 0 0 2.5h1.25a1.25 1.25 0 0 0 0-2.5z" fill="#697386"></path></g></svg>',
			'is_popular' => true,
		),
		'ach-debit'  => array(
			'name'       => __( 'ACH Direct Debit', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#e3e8ee"/><path d="M7.274 13.5a1.25 1.25 0 0 1-.649-2.333C7.024 10.937 10.15 9.215 16 6c5.851 3.215 8.976 4.937 9.375 5.167a1.25 1.25 0 0 1-.65 2.333zm12.476 10v-8.125h3.75V23.5H25a1 1 0 0 1 1 1V26H6v-1.5a1 1 0 0 1 1-1h1.5v-8.125h3.75V23.5h1.875v-8.125h3.75V23.5z" fill="#697386"/></g></svg>',
			'is_popular' => true,
		),
		'sepa-debit' => array(
			'name'       => __( 'SEPA Direct Debit', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#10298d"/><path d="M27.485 18.42h-2.749l-.37 1.342H22.24L24.533 12h3.104l2.325 7.762h-2.083l-.393-1.342zm-.408-1.512-.963-3.364-.936 3.364zm-10.452 2.854V12h3.83c.526 0 .928.044 1.203.13.63.202 1.052.612 1.27 1.233.111.325.167.816.167 1.47 0 .788-.06 1.354-.183 1.699-.247.68-.753 1.072-1.517 1.175-.09.015-.472.028-1.146.04l-.341.011H18.68v2.004zm2.056-3.805h1.282c.407-.015.653-.047.744-.096.12-.068.202-.204.242-.408.026-.136.04-.337.04-.604 0-.329-.026-.573-.079-.732-.073-.222-.25-.358-.53-.407a3.91 3.91 0 0 0-.4-.011h-1.299zm-10.469-1.48H6.3c0-.32-.038-.534-.11-.642-.114-.162-.43-.242-.942-.242-.5 0-.831.046-.993.139-.161.093-.242.296-.242.608 0 .283.072.469.215.558a.91.91 0 0 0 .408.112l.386.026c.517.033 1.033.072 1.55.119.654.066 1.126.243 1.421.53.231.222.37.515.414.875.025.216.037.46.037.73 0 .626-.057 1.083-.175 1.374-.213.532-.693.868-1.437 1.009-.312.06-.788.089-1.43.089-1.072 0-1.819-.064-2.24-.196-.517-.158-.858-.482-1.024-.969-.092-.269-.137-.72-.137-1.353h1.914v.162c0 .337.096.554.287.65.13.067.29.101.477.106h.704c.359 0 .587-.019.687-.056a.57.57 0 0 0 .346-.34 1.38 1.38 0 0 0 .044-.374c0-.341-.123-.55-.368-.624-.092-.03-.52-.071-1.28-.123a15.411 15.411 0 0 1-1.274-.128c-.626-.119-1.044-.364-1.252-.736-.184-.315-.275-.793-.275-1.432 0-.487.05-.877.148-1.17.1-.294.258-.517.48-.669.321-.234.735-.371 1.237-.412.463-.04.927-.058 1.391-.056.803 0 1.375.046 1.717.14.833.227 1.248.863 1.248 1.909a5.8 5.8 0 0 1-.018.385z" fill="#fff"/><path d="M13.786 13.092c.849 0 1.605.398 2.103 1.02l.444-.966a3.855 3.855 0 0 0-2.678-1.077c-1.62 0-3.006.995-3.575 2.402h-.865l-.51 1.111h1.111c-.018.23-.017.46.006.69h-.56l-.51 1.111h1.354a3.853 3.853 0 0 0 3.549 2.335c.803 0 1.55-.244 2.167-.662v-1.363a2.683 2.683 0 0 1-2.036.939 2.7 2.7 0 0 1-2.266-1.248h2.832l.511-1.112h-3.761a2.886 2.886 0 0 1-.016-.69h4.093l.51-1.11h-4.25a2.704 2.704 0 0 1 2.347-1.38" fill="#ffcc02"/></g></svg>',
			'is_popular' => true,
		),
		'alipay'     => array(
			'name'       => __( 'Alipay', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#1c9fe5"/><path d="M23.104 18.98a142.494 142.494 0 0 0 11.052 3.848c2.044.85 0 5.668-2.159 4.674-2.444-1.066-7.359-3.245-11.097-5.108C18.822 24.842 15.556 28 10.907 28 6.775 28 4 25.568 4 21.943c0-3.053 2.11-6.137 6.82-6.137 2.697 0 5.47.766 8.785 1.922a25.007 25.007 0 0 0 1.529-3.838l-11.981-.006v-1.848l6.162.015V9.63H7.808V7.81l7.507.006V5.115c0-.708.38-1.115 1.042-1.115h3.14v3.827l7.442.005v1.805h-7.44v2.431l6.088.016s-.754 3.904-2.483 6.897zM5.691 21.79v-.004c0 1.736 1.351 3.489 4.64 3.489 2.54 0 5.028-1.52 7.408-4.522-3.181-1.592-4.886-2.357-7.348-2.357-2.394 0-4.7 1.164-4.7 3.394z" fill="#fff" fill-rule="nonzero"/></g></svg>',
			'is_popular' => false,
		),
		'bancontact' => array(
			'name'       => __( 'Bancontact', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"/><g fill-rule="nonzero"><path d="M25.64 14.412h-7.664l-.783.896-2.525 2.898-.783.896H6.331l.764-.906.362-.428.763-.907H4.746c-.636 0-1.155.548-1.155 1.205v2.55c0 .666.52 1.204 1.155 1.204h13.328c.637 0 1.508-.398 1.928-.896l2.016-2.33z" fill="#005498"/><path d="M27.176 11.694c.636 0 1.154.548 1.154 1.205v2.539c0 .667-.518 1.204-1.154 1.204H23.71l.773-.896.382-.448.773-.896h-7.662l-4.081 4.68H6.292l5.451-6.273.206-.239c.43-.488 1.301-.896 1.937-.896h13.29z" fill="#ffbf00"/></g></g></svg>',
			'is_popular' => false,
		),
		'fpx'        => array(
			'name'       => __( 'FPX', 'stripe' ),
			'icon'       => '<svg width="32" height="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M29.174 13.9757c-2.8569-3.6805-5.7383-7.34192-8.644-10.98397-.46-.578-1.132-1.27-1.916-.876-.53.264-1.012 1.05-1.066 1.64-.178 1.938-.164 3.89-.184 5.836-.002.22.22.45997.38.66197 1.208 1.542 2.436 3.07 3.636 4.616.334.43.52.78.58 1.114-.06.468-.246.704-.58 1.134-1.2 1.546-2.428 3.08-3.636 4.622-.16.204-.382.452-.38.672.02 1.946.006 3.898.184 5.834.054.59.536 1.376 1.066 1.64.784.392 1.456-.304 1.916-.882 2.9059-3.6446 5.7872-7.3087 8.644-10.992.508-.654.776-1.092.826-2.028-.05-.68-.32-1.354-.826-2.008Z" fill="#1F2C5C"/><path fill-rule="evenodd" clip-rule="evenodd" d="M2.826 13.9757c2.852-3.68 5.74-7.33797 8.644-10.98397.46-.578 1.132-1.27 1.916-.876.53.264 1.012 1.05 1.066 1.64.178 1.938.164 3.89.184 5.836.002.22-.22.45997-.38.66197-1.208 1.542-2.436 3.07-3.636 4.616-.334.43-.522.78-.58 1.114.058.468.246.704.58 1.134 1.2 1.546 2.428 3.08 3.636 4.622.16.204.382.452.38.672-.02 1.946-.006 3.898-.184 5.834-.054.59-.536 1.376-1.066 1.64-.784.392-1.456-.304-1.916-.882-2.90579-3.6447-5.78719-7.3088-8.644-10.992-.508-.654-.776-1.092-.826-2.028.05-.68.32-1.354.826-2.008Z" fill="#1A8ACB"/></svg>',
			'is_popular' => false,
		),
		'giropay'    => array(
			'name'       => __( 'Giropay', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"/><path d="M4 11.191C4 9.705 5.239 8.5 6.766 8.5h18.468C26.762 8.5 28 9.705 28 11.191v9.618c0 1.486-1.238 2.691-2.766 2.691H6.766C5.239 23.5 4 22.295 4 20.809zm1.02 9.6c0 .944.783 1.71 1.75 1.71h9.213V9.5H6.77c-.967 0-1.75.765-1.75 1.708v9.584zm13.749-.104h2.272v-3.57h.025c.43.782 1.29 1.072 2.084 1.072 1.957 0 3.004-1.615 3.004-3.558 0-1.589-.997-3.319-2.815-3.319-1.035 0-1.994.417-2.45 1.338h-.025v-1.185h-2.095zm5.037-6.005c0 1.047-.518 1.766-1.376 1.766-.758 0-1.39-.72-1.39-1.678 0-.984.556-1.716 1.39-1.716.885 0 1.376.757 1.376 1.627z" fill="#04337b"/><path d="M14.153 11.463v5.71c0 2.657-1.33 3.515-4.017 3.515a7.958 7.958 0 0 1-2.547-.41l.115-1.764c.703.335 1.292.533 2.253.533 1.33 0 2.047-.607 2.047-1.874v-.348h-.026c-.55.757-1.318 1.105-2.24 1.105-1.83 0-2.969-1.34-2.969-3.252 0-1.924.935-3.366 3.007-3.366.985 0 1.78.523 2.267 1.318h.025v-1.168zM9.15 14.64c0 1.005.616 1.576 1.306 1.576.818 0 1.472-.67 1.472-1.664 0-.72-.435-1.527-1.472-1.527-.857 0-1.306.734-1.306 1.615z" fill="#ee3525"/></g></svg>',
			'is_popular' => false,
		),
		'ideal'      => array(
			'name'       => __( 'iDEAL', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none"><path fill="#FFF" d="M0 0h32v32H0z"/><g transform="translate(3 5)"><path d="M0 1.694v19.464c0 .936.758 1.694 1.694 1.694h11.63c8.788 0 12.599-4.922 12.599-11.448C25.923 4.903 22.112 0 13.323 0H1.694C.759 0 0 .758 0 1.694z" fill="#FFF"/><path d="M13.321 21.296H3.206A1.628 1.628 0 0 1 1.58 19.67V3.182c.001-.898.729-1.625 1.626-1.626h10.115c9.593 0 11.026 6.17 11.026 9.848 0 6.381-3.916 9.892-11.026 9.892zM3.206 2.098c-.598 0-1.084.485-1.085 1.084V19.67c.001.599.487 1.084 1.085 1.084h10.115c6.76 0 10.484-3.32 10.484-9.35 0-8.097-6.569-9.306-10.484-9.306H3.206z" fill="#000"/><path d="M7.781 4.78v14.377h6.259c5.686 0 8.151-3.213 8.151-7.746 0-4.342-2.465-7.716-8.151-7.716H8.865c-.598 0-1.084.485-1.084 1.084z" fill="#C06"/><path fill="#FFF" d="M19.713 9.47v2.8h1.674v.635h-2.429V9.47zm-2.514 0 1.285 3.435H17.7l-.26-.762h-1.285l-.27.762h-.762l1.3-3.435h.776zm.043 2.107-.433-1.26H16.8l-.447 1.26h.89zm-2.63-2.107v.635h-1.814v.736h1.665v.587h-1.665v.842h1.853v.635h-2.607V9.47zm-4.627 0c.21-.002.42.034.617.106.187.068.356.176.496.318.146.15.257.331.328.529.082.24.122.492.117.746.002.234-.03.467-.096.692-.059.2-.157.387-.29.549-.133.156-.3.28-.487.363-.216.093-.45.138-.685.132H8.503V9.47h1.482zm-.053 2.8a.983.983 0 0 0 .317-.053.703.703 0 0 0 .275-.176.888.888 0 0 0 .192-.319c.052-.155.076-.318.072-.481a2.04 2.04 0 0 0-.05-.47.932.932 0 0 0-.17-.357.74.74 0 0 0-.305-.23 1.212 1.212 0 0 0-.47-.079h-.538v2.165h.677z"/><path d="M4.953 13.683a1.2 1.2 0 0 1 1.2 1.2v4.274a2.401 2.401 0 0 1-2.401-2.401v-1.872a1.2 1.2 0 0 1 1.2-1.2z" fill="#000"/><circle fill="#000" cx="4.953" cy="11.188" r="1.585"/></g></g></svg>',
			'is_popular' => false,
		),
		'klarna'     => array(
			'name'       => __( 'Klarna', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#ffb3c7"/><path d="M16.279 7c0 3.307-1.501 6.342-4.124 8.323l-1.573 1.2 6.126 8.442h5.034l-5.638-7.77C18.777 14.504 20.27 10.888 20.27 7zM6 7h4.087v17.965H6zm16.382 15.665c0-1.289 1.034-2.335 2.309-2.335S27 21.376 27 22.665C27 23.955 25.966 25 24.69 25s-2.308-1.046-2.308-2.335z" fill="#0a0b09" fill-rule="nonzero"/></g></svg>',
			'is_popular' => false,
		),
		'afterpay'   => array(
			'name'       => __( 'Afterpay / Clearpay', 'stripe' ),
			'icon'       => '<svg height="16" width="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16Z" fill="#B2FCE4"/><path d="m12.563 5.187-1.477-.845-1.498-.859c-.99-.567-2.228.146-2.228 1.29v.192a.29.29 0 0 0 .15.256l.695.397a.288.288 0 0 0 .431-.252V4.91c0-.226.243-.367.44-.256l1.366.786 1.362.78a.293.293 0 0 1 0 .509l-1.362.781-1.366.786a.294.294 0 0 1-.44-.257v-.226c0-1.144-1.238-1.861-2.228-1.29l-1.494.863-1.478.846a1.49 1.49 0 0 0 0 2.582l1.478.845 1.498.859c.99.567 2.228-.146 2.228-1.29v-.192a.29.29 0 0 0-.15-.256l-.695-.397a.288.288 0 0 0-.431.252v.457a.294.294 0 0 1-.44.256l-1.366-.786-1.362-.78a.293.293 0 0 1 0-.509l1.362-.781 1.366-.786c.197-.11.44.03.44.257v.226c0 1.144 1.238 1.861 2.228 1.289l1.499-.858 1.477-.845c.99-.577.99-2.015-.005-2.587Z"/></svg>',
			'is_popular' => true,
		),
		'p24'        => array(
			'name'       => __( 'Przelewy24', 'stripe' ),
			'icon'       => '<svg height="32" width="32" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg"><g fill="none" fill-rule="evenodd"><path d="M0 0h32v32H0z" fill="#fff"/><path d="m18.28 21.961-.155.817h-6.556l.308-1.615c.172-.906.47-1.499.898-1.78.427-.28 1.355-.496 2.784-.647 1.142-.117 1.85-.276 2.12-.478.273-.2.488-.722.648-1.564.14-.738.102-1.217-.117-1.437-.219-.22-.765-.33-1.641-.33-1.094 0-1.8.09-2.12.267-.322.178-.547.607-.675 1.286l-.11.64h-1.007l.092-.445c.195-1.027.555-1.711 1.08-2.053.526-.34 1.48-.512 2.862-.512 1.226 0 2.017.184 2.369.552.352.37.426 1.088.223 2.157-.195 1.027-.521 1.713-.98 2.06-.46.344-1.359.578-2.698.7-1.176.108-1.894.26-2.153.453-.26.192-.474.739-.645 1.64l-.055.29zm8.623-7.748-1.1 5.783h1.362l-.156.817h-1.36l-.377 1.98h-1.025l.376-1.98H19.53l.215-1.137 5.573-5.463h1.587zm-2.126 5.783.981-5.16h-.02l-5.208 5.16z" fill="#99a0a6"/><path d="m3 22.762 1.656-8.652h4.269c1.051 0 1.733.188 2.043.564.31.376.367 1.08.17 2.111-.19.989-.518 1.663-.985 2.021-.467.36-1.25.54-2.346.54l-.411.006H4.705l-.653 3.41zm1.862-4.234h2.493c1.042 0 1.73-.1 2.062-.298.332-.198.566-.655.702-1.369.16-.837.163-1.366.007-1.588-.156-.221-.604-.333-1.347-.333l-.401-.006H5.55z" fill="#d40e2b"/><path d="m9.143 10.96-1.013-.671a22.123 22.123 0 0 1 3.717-1.386l.186.914c-.915.26-1.88.632-2.89 1.143zm11.48-.502a10.83 10.83 0 0 0-2.991-1.001L18.449 8h.023c2.362.011 4.24.308 5.72.722l-3.569 1.736zm-13.414.301 1.034.7c-.471.27-.953.571-1.443.905H4.793s.83-.737 2.415-1.605zm10.026-2.708-.484 1.29a12.352 12.352 0 0 0-4.016.264l-.138-.924c1.52-.358 3.074-.57 4.638-.631zm8.84 1.304C28.215 10.293 29 11.36 29 11.36h-6.92s-.228-.198-.659-.473z" fill="#99a0a6"/></g></svg>',
			'is_popular' => false,
		),
	);

	?>

	<table>
		<tr class="simpay-panel-field">
			<th>
				<div style="display: flex; align-items: center;">
					<strong>
						<?php esc_html_e( 'Payment Methods', 'stripe' ); ?>
					</strong>
					<select
						class="simpay-panel-field-payment-method-filter"
						style="font-size: 12px; min-height: 26px; margin-left: 10px; font-weight: normal;"
					>
						<option value="popular">
							<?php esc_html_e( 'Popular', 'stripe' ); ?>
						</option>
						<option value="all">
							<?php esc_html_e( 'All', 'stripe' ); ?>
						</option>
					</select>
				</div>
			</th>
			<td>
				<div class="simpay-payment-methods">
				<?php
				foreach ( $payment_methods as $payment_method_id => $payment_method ) :
					$upgrade_title = sprintf(
						/* translators: %s Payment Method name. */
						esc_html__(
							'Unlock the "%s" Payment Method',
							'stripe'
						),
						$payment_method['name']
					);

					$upgrade_description = sprintf(
						/* translators: %1$s Payment method name. */
						__(
							'We\'re sorry, the %1$s payment method is not available in WP Simple Pay Lite. Please upgrade to <strong>WP Simple Pay Pro</strong> to unlock this and other awesome features.',
							'stripe'
						),
						(
							'<strong>' .
							$payment_method['name'] .
							'</strong>'
						)
					);

					$upgrade_url = simpay_pro_upgrade_url(
						'form-payment-method-settings',
						sprintf( '%s Payment Method', $payment_method['name'] )
					);

					$upgrade_purchased_url = simpay_docs_link(
						sprintf(
							'%s Payment Method (already purchased)',
							$payment_method['name']
						),
						'upgrading-wp-simple-pay-lite-to-pro',
						'form-payment-method-settings',
						true
					);
					?>
					<div
						class="simpay-panel-field-payment-method"
						style="display: <?php echo esc_attr( $payment_method['is_popular'] ? 'block' : 'none' ); ?>"
						data-payment-method='{ "scope": "<?php echo esc_html( $payment_method['is_popular'] ? 'popular' : '' ); ?>", "licenses": "personal" }'
					>
						<label for="payment-method-<?php echo esc_attr( $payment_method_id ); ?>">
							<div style="display: flex; align-items: center;">
								<div class="simpay-panel-field-payment-method__icon">
									<?php echo $payment_method['icon']; // WPCS: XSS ok. ?>
								</div>

								<input
									type="checkbox"
									id="payment-method-<?php echo esc_attr( $payment_method_id ); ?>"
									class="simpay-field simpay-field-checkbox simpay-field simpay-field-checkboxes simpay-payment-method-lite"
									<?php checked( true, 'card' === $payment_method_id ); ?>
									<?php if ( 'card' === $payment_method_id ) : ?>
									disabled
									<?php else : ?>
									data-available="no"
									data-upgrade-title="<?php echo esc_attr( $upgrade_title ); ?>"
									data-upgrade-description="<?php echo esc_attr( $upgrade_description ); ?>"
									data-upgrade-url="<?php echo esc_url( $upgrade_url ); ?>"
									data-upgrade-purchased-url="<?php echo esc_url( $upgrade_purchased_url ); ?>"
									<?php endif; ?>
								>
								<?php echo esc_html( $payment_method['name'] ); ?>

								<a
									href="https://docs.wpsimplepay.com/categories/payment-methods/"
									target="_blank"
									rel="noopener noreferrer"
									class="simpay-panel-field-payment-method__help"
									style="margin-left: auto;"
								>
									<span class="dashicons dashicons-editor-help"></span>
									<span class="screen-reader-text">
										<?php esc_html_e( 'Learn about Payment Method', 'stripe' ); ?>
									</span>
								</a>
							</div>
						</label>
					</div>
				<?php endforeach; ?>
				</div>
			</td>
		</tr>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__unstable_add_payment_methods'
);

/**
 * Adds "Tax Rates" upgrade placeholder setting.
 *
 * @since 4.4.0
 *
 * @return void
 */
function __add_tax_rates_upsell() {
	if ( class_exists( '\SimplePay\Pro\Lite_Helper', false ) ) {
		return;
	}
	?>

	<table>
		<tr class="simpay-panel-field">
			<th>
				<label for="_tax_rates">
					<?php esc_html_e( 'Tax Rates', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<?php
				$upgrade_url = simpay_pro_upgrade_url(
					'payment-form-payment-settings',
					'Upgrade to WP Simple Pay Pro to collect taxes or additional fees on payments.'
				);

				echo wp_kses(
					sprintf(
						'<span class="dashicons dashicons-no"></span>%s - ',
						__( 'Disabled', 'stripe' )
					),
					array(
						'span' => array(
							'class' => true,
						),
					)
				);

				echo wp_kses(
					sprintf(
						/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
						__(
							'%1$sUpgrade to WP Simple Pay Pro%2$s to collect taxes or additional fees on payments.',
							'stripe'
						),
						'<a href="' . esc_url( $upgrade_url ) . '" target="_blank" rel="noopener noreferrer">',
						'</a>'
					),
					array(
						'a'    => array(
							'href'   => true,
							'target' => true,
							'rel'    => true,
						),
						'span' => array(
							'class' => true,
						),
					)
				);
				?>
			</td>
		</tr>
	</table>

	<?php
}
add_action(
	'simpay_form_settings_meta_payment_options_panel',
	__NAMESPACE__ . '\\__add_tax_rates_upsell',
	10.5
);
