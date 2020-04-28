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
	$currency_position = simpay_get_currency_position();
?>

<table class="simpay-show-if" data-if="_subscription_type" data-is="disabled">
	<tbody class="simpay-panel-section">
		<?php
		/**
		 * Allows output before the "Amount" field in
		 * "Payment Options" Payment Form settings tab content.
		 *
		 * @since 3.0
		 *
		 * @param int $post_id Current Payment Form ID.
		 */
		do_action( 'simpay_amount_options', $post_id );
		?>

		<tr class="simpay-panel-field simpay-show-if" data-if="_amount_type" data-is="one_time_set">
			<th>
				<label for="_amount">
					<?php esc_html_e( 'One-Time Amount', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<div class="simpay-currency-field">
					<?php if ( in_array( $currency_position, array( 'left', 'left_space' ), true ) ) : ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-left">
							<?php echo simpay_get_saved_currency_symbol(); ?>
						</span>
					<?php endif; ?>

					<?php
					$amount = simpay_get_saved_meta( $post_id, '_amount', simpay_global_minimum_amount() );

					$classes = array(
						'simpay-field-tiny',
						'simpay-amount-input',
						'simpay-minimum-amount-required',
					);

					simpay_print_field(
						array(
							'type'        => 'standard',
							'subtype'     => 'tel',
							'name'        => '_amount',
							'id'          => '_amount',
							'value'       => $amount,
							'class'       => $classes,
							'placeholder' => simpay_format_currency( simpay_global_minimum_amount(), simpay_get_setting( 'currency' ), false ),
						)
					);
					?>

					<?php if ( in_array( $currency_position, array( 'right', 'right_space' ), true ) ) : ?>
						<span class="simpay-currency-symbol simpay-currency-symbol-right">
							<?php echo simpay_get_saved_currency_symbol(); ?>
						</span>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<?php
/**
 * Allows further output after the "Amount" field in
 * "Payment Options" Payment Form settings tab content.
 *
 * @since 3.0
 */
do_action( 'simpay_admin_after_amount_options', $post_id );
?>

<table>
	<tbody class="simpay-panel-section">

		<tr class="simpay-panel-field">
			<th>
				<label for="_success_redirect_type">
					<?php esc_html_e( 'Payment Success Page', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<?php
				$success_redirect_type = simpay_get_saved_meta( $post_id, '_success_redirect_type', 'default' );

				simpay_print_field(
					array(
						'type'    => 'radio',
						'name'    => '_success_redirect_type',
						'id'      => '_success_redirect_type',
						'class'   => array( 'simpay-multi-toggle' ),
						'options' => array(
							'default'  => __( 'Global Setting', 'stripe' ),
							'page'     => __( 'Specific Page', 'stripe' ),
							'redirect' => __( 'Redirect URL', 'stripe' ),
						),
						'inline'  => 'inline',
						'default' => 'default',
						'value'   => $success_redirect_type,
					)
				);
				?>

				<div class="simpay-show-if" data-if="_success_redirect_type" data-is="default">
					<p class="description">
						<?php _e( 'By default, the payment success page indicated in Simple Pay > Settings > General will be used. This option allows you to specify an alternate page or URL for this payment form only.', 'stripe' ); ?>
					</p>
				</div>

				<div class="simpay-show-if" data-if="_success_redirect_type" data-is="page" style="margin-top: 8px;">
					<?php
					simpay_print_field(
						array(
							'type'        => 'select',
							'page_select' => 'page_select',
							'name'        => '_success_redirect_page',
							'id'          => '_success_redirect_page',
							'value'       => simpay_get_saved_meta( $post_id, '_success_redirect_page', '' ),
							'description' => __( 'Choose a page from your site to redirect to after a successful transaction.', 'stripe' ),
						)
					);
					?>
				</div>

				<div class="simpay-show-if" data-if="_success_redirect_type" data-is="redirect" style="margin-top: 8px;">
					<?php
					simpay_print_field(
						array(
							'type'        => 'standard',
							'subtype'     => 'text',
							'name'        => '_success_redirect_url',
							'id'          => '_success_redirect_url',
							'class'       => array(
								'simpay-field-text',
							),
							'placeholder' => 'https://',
							'value'       => simpay_get_saved_meta( $post_id, '_success_redirect_url', '' ),
							'description' => __( 'Enter a custom redirect URL for successful transactions.', 'stripe' ),
						)
					);
					?>
				</div>
			</td>
		</tr>
	</tbody>
</table>

<?php
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
