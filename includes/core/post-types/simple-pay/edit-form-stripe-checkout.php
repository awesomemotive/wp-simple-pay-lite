<?php
/**
 * Simple Pay: Edit form Stripe Checkout
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
 * Adds "Stripe Checkout" Payment Form settings tab content.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_stripe_checkout( $post_id ) {
	?>
<table>
	<tbody class="simpay-panel-section">

		<?php
		/**
		 * Allows output at the top of "Stripe Checkout" Payment Form
		 * settings tab content.
		 *
		 * @since 3.4.0
		 * @since 3.8.0 Add $post_id parameter.
		 *
		 * @param int $post_id Current Payment Form ID.
		 */
		do_action( 'simpay_admin_before_stripe_checkout_rows', $post_id );
		?>

		<tr class="simpay-panel-field simpay-show-if" data-if="_subscription_type" data-is="disabled">
			<th>
				<label for="_image_url">
					<?php esc_html_e( 'Image', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<?php
				$image_url = simpay_get_saved_meta( $post_id, '_image_url' );

				simpay_print_field(
					array(
						'type'    => 'standard',
						'subtype' => 'url',
						'name'    => '_image_url',
						'id'      => '_image_url',
						'value'   => $image_url,
						'class'   => array(
							'simpay-field-text',
						),
					)
				);
				?>

				<br />

				<button type="button" class="simpay-media-uploader button button-secondary" style="margin-top: 4px;"><?php esc_html_e( 'Choose Image', 'stripe' ); ?></button>

				<p class="description">
					<?php esc_html_e( 'Image to show on the Stripe.com Checkout page.', 'stripe' ); ?>
				</p>

				<div class="simpay-image-preview-wrap <?php echo( empty( $image_url ) ? 'simpay-panel-hidden' : '' ); ?>">
					<a href="#" class="simpay-remove-image-preview simpay-remove-icon" aria-label="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>" title="<?php esc_attr_e( 'Remove image', 'stripe' ); ?>"></a>
					<img src="<?php echo esc_attr( $image_url ); ?>" class="simpay-image-preview" />
				</div>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<?php esc_html_e( 'Submit Button Color', 'stripe' ); ?>
			</th>
			<td>
				<p class="description">
					<?php
					echo wp_kses(
						sprintf(
							__( 'Adjust the Stripe Checkout submit button color in the Stripe %1$sBranding settings%2$s', 'stripe' ),
							'<a href="https://dashboard.stripe.com/account/branding" target="_blank" rel="noopener noreferrer">',
							'</a>'
						),
						array(
							'a' => array(
								'href'   => true,
								'target' => true,
								'rel'    => true,
							),
						)
					);
					?>
				</p>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_checkout_submit_type">
					<?php esc_html_e( 'Submit Button Type', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<?php
				$checkout_submit_type = simpay_get_saved_meta( $post_id, '_checkout_submit_type', 'pay' );

				simpay_print_field(
					array(
						'type'        => 'select',
						'name'        => '_checkout_submit_type',
						'id'          => '_checkout_submit_type',
						'value'       => $checkout_submit_type,
						'options'     => array(
							'book'   => esc_html__( 'Booking', 'stripe' ),
							'donate' => esc_html__( 'Donate', 'stripe' ),
							'pay'    => esc_html__( 'Pay', 'stripe' ),
						),
						'description' => esc_html__( 'Describes the type of transaction being performed by Checkout in order to customize relevant text on the page, such as the submit button.', 'stripe' ),
					)
				);
				?>
			</td>
		</tr>

		<?php
		/**
		 * Allows output somewhere in the middle of "Stripe Checkout" Payment Form
		 * settings tab content.
		 *
		 * @since 3.0.0
		 *
		 * @param int $form_id Current Payment Form ID.
		 */
		do_action( 'simpay_after_checkout_button_text', $post_id );
		?>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_shipping_address"><?php esc_html_e( 'Require Shipping Address', 'stripe' ); ?></label>
			</th>
			<td>
				<?php
				$enable_shipping_address = simpay_get_saved_meta( $post_id, '_enable_shipping_address', 'no' );

				simpay_print_field(
					array(
						'type'        => 'checkbox',
						'name'        => '_enable_shipping_address',
						'id'          => '_enable_shipping_address',
						'value'       => $enable_shipping_address,
						'description' => esc_html__( 'If enabled, Checkout will always collect the customer’s shipping address.', 'stripe' ),
					)
				);
				?>
			</td>
		</tr>

		<tr class="simpay-panel-field">
			<th>
				<label for="_enable_billing_address">
					<?php esc_html_e( 'Require Billing Address', 'stripe' ); ?>
				</label>
			</th>
			<td>
				<?php
				$enable_billing_address = simpay_get_saved_meta( $post_id, '_enable_billing_address', 'no' );

				simpay_print_field(
					array(
						'type'        => 'checkbox',
						'name'        => '_enable_billing_address',
						'id'          => '_enable_billing_address',
						'value'       => $enable_billing_address,
						'description' => esc_html__( 'If enabled, Checkout will always collect the customer’s billing address. If not, Checkout will only collect the billing address when necessary.', 'stripe' ),
					)
				);
				?>
			</td>
		</tr>

		<?php
		/**
		 * Allows further output at the bottom of "Stripe Checkout" Payment Form
		 * settings tab content.
		 *
		 * @since 3.4.0
		 */
		do_action( 'simpay_admin_after_stripe_checkout_rows' );
		?>

	</tbody>
</table>

	<?php
	/**
	 * Allows output at the top of "Stripe Checkout" Payment Form
	 * settings tab content.
	 *
	 * @since 3.0.0
	 */
	do_action( 'simpay_admin_after_stripe_checkout' );
}
add_action( 'simpay_form_settings_meta_stripe_checkout_panel', __NAMESPACE__ . '\\add_stripe_checkout' );

/**
 * Outputs "Company Name" and "Item Description" fields in the
 * "Stripe Checkout Display" Payment Form settings tab.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_company_info( $post_id ) {
	$form_display_type = simpay_get_saved_meta( $post_id, '_form_display_type', 'embedded' );
	?>

<tr class="simpay-panel-field">
	<th>
		<label for="_company_name">
			<?php esc_html_e( 'Company Name', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		$company_name = simpay_get_saved_meta( $post_id, '_company_name', false );

		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_company_name',
				'id'          => '_company_name',
				'value'       => false !== $company_name ? $company_name : get_bloginfo( 'name' ),
				'class'       => array(
					'simpay-field-text',
				),
				'description' => __( 'Also used for the form heading.', 'stripe' ),
			)
		);
		?>
	</td>
</tr>

<tr class="simpay-panel-field">
	<th>
		<label for="_item_description">
			<?php esc_html_e( 'Item Description', 'stripe' ); ?>
		</label>
	</th>
	<td>
		<?php
		simpay_print_field(
			array(
				'type'        => 'standard',
				'subtype'     => 'text',
				'name'        => '_item_description',
				'id'          => '_item_description',
				'value'       => simpay_get_saved_meta( $post_id, '_item_description' ),
				'class'       => array(
					'simpay-field-text',
				),
				'description' => __(
					'Also used for the form subheading.',
					'stripe'
				),
			)
		);
		?>
	</td>
</tr>

	<?php
}
add_action( 'simpay_admin_before_stripe_checkout_rows', __NAMESPACE__ . '\\add_company_info', 20 );

/**
 * Outputs a link back to "Custom Form Fields" when viewing the
 * "Stripe Checkout Display" Payment Form settings tab.
 *
 * @since 3.8.0
 */
function add_custom_form_fields_link() {
	$message = sprintf(
		__( 'Configure the on-site Payment Button in the %1$sOn-Site Form Display%2$s settings.', 'stripe' ),
		'<a href="#" class="simpay-tab-link" data-show-tab="simpay-form_display">',
		'</a>'
	);
	?>

<tr class="simpay-panel-field">
	<td>
		<div class="notice inline notice-info" style="margin-top: 18px;">
			<p>
				<?php echo wp_kses_post( $message ); ?>
			</p>
		</div>
	</td>
</tr>

	<?php
}
add_action( 'simpay_admin_before_stripe_checkout_rows', __NAMESPACE__ . '\\add_custom_form_fields_link' );
