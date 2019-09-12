<?php

namespace SimplePay\Core\Admin\Metaboxes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	private static $saved_meta_boxes = false;

	/**
	 * Output the meta box markup.
	 *
	 * @since 3.0.0
	 *
	 * @param \WP_Post $post
	 */
	public static function html( $post ) {

		// @todo Don't use a static method and allow this class to properly register hooks.
		add_action( 'simpay_admin_before_stripe_checkout_rows', array( __CLASS__, 'add_company_info_settings' ) );

		// Add link back to Custom Form Fields in the Stripe Checkout Display tab.
		add_action( 'simpay_after_checkout_button_text', array( __CLASS__, 'add_custom_form_fields_link' ), 5 );

		// @see Meta_Boxes::save_meta_boxes()
		wp_nonce_field( 'simpay_save_data', 'simpay_meta_nonce' );

		// Used in the include files
		$position = simpay_get_currency_position();

		?>
		<div class="simpay-panels-wrap">

			<ul class="simpay-tabs">
				<?php self::settings_tabs( $post ); ?>
			</ul>

			<div class="simpay-panels">

				<!-- Payment Options Tab -->
				<div id="payment-options-settings-panel" class="simpay-panel">
					<?php

					$payment_options_template = apply_filters( 'simpay_payment_options_template', SIMPLE_PAY_INC . 'core/admin/metaboxes/views/tabs/tab-payment-options.php' );

					if ( file_exists( $payment_options_template ) ) {
						include_once( $payment_options_template );
					}

					do_action( 'simpay_form_settings_meta_payment_options_panel', $post->ID );

					?>
				</div>

				<!-- Form Display Options Tab -->
				<div id="form-display-options-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$form_display_options_template = apply_filters( 'simpay_form_options_template', '' );

					if ( file_exists( $form_display_options_template ) ) {
						include( $form_display_options_template );
					}

					?>
				</div>

				<!-- Custom Form Fields Options Tab -->
				<div id="custom-form-fields-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$form_display_template = apply_filters( 'simpay_form_display_template', SIMPLE_PAY_INC . 'core/admin/metaboxes/views/tabs/tab-custom-form-fields.php' );

					if ( file_exists( $form_display_template ) ) {
						include_once( $form_display_template );
					}

					do_action( 'simpay_form_settings_meta_form_display_panel', $post->ID );

					?>
				</div>

				<!-- Stripe Checkout Display Options Tab -->
				<div id="stripe-checkout-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$stripe_checkout_template = apply_filters( 'simpay_stripe_checkout_template', SIMPLE_PAY_INC . 'core/admin/metaboxes/views/tabs/tab-stripe-checkout.php' );

					if ( file_exists( $stripe_checkout_template ) ) {
						include_once( $stripe_checkout_template );
					}

					do_action( 'simpay_form_settings_meta_stripe_checkout_panel', $post->ID );

					?>
				</div>

				<!-- Subscription Options Tab -->
				<div id="subscription-options-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$subscription_options_template = apply_filters( 'simpay_subscription_options_template', SIMPLE_PAY_INC . 'core/admin/metaboxes/views/tabs/tab-subscription-options.php' );

					if ( file_exists( $subscription_options_template ) ) {
						include_once( $subscription_options_template );
					}

					do_action( 'simpay_form_settings_meta_subscription_display_panel', $post->ID );
					?>
				</div>

		    <?php do_action( 'simpay_form_settings_meta_options_panel', $post->ID ); ?>

			</div>

			<div class="clear"></div>
		</div>
		<?php

	}

	/**
	 * Print settings tabs.
	 */
	private static function settings_tabs( $post ) {

		// Force shown (add "active" class) for first tab.

		// Hook to add more tabs.
		$tabs = apply_filters( 'simpay_form_settings_meta_tabs_li', array(
			'payment_options'      => array(
				'label'  => esc_html__( 'Payment Options', 'stripe' ),
				'target' => 'payment-options-settings-panel',
				'class'  => array( 'active' ),
				'icon'   => '',
			),
			'form_display'         => array(
				'label'  => esc_html__( 'On-Page Form Display', 'stripe' ),
				'target' => 'custom-form-fields-settings-panel',
				'class'  => array(),
				'icon'   => '',
			),
			'stripe_checkout'      => array(
				'label'  => esc_html__( 'Stripe Checkout Display', 'stripe' ),
				'target' => 'stripe-checkout-settings-panel',
				'class'  => array( 'toggle-_form_display_type-stripe_checkout' ),
				'icon'   => '',
			),
			'subscription_options' => array(
				'label'  => esc_html__( 'Subscription Options', 'stripe' ),
				'target' => 'subscription-options-settings-panel',
				'class'  => array(),
				'icon'   => '',
			),
		), $post->ID );

		// Output the tabs as list items.
		if ( ! empty( $tabs ) && is_array( $tabs ) ) {

			foreach ( $tabs as $key => $tab ) {

				if ( isset( $tab['target'] ) && isset( $tab['label'] ) ) {

					$icon       = $tab['icon'] ? $tab['icon'] : '';
					$class      = $tab['class'] ? $tab['class'] : array();
					$inner_html = '<a href="#' . $tab['target'] . '" class="simpay-tab-item"><i class="' . $icon . '" ></i> <span>' . esc_html( $tab['label'] ) . '</span></a>';

					echo '<li class="simpay-' . $key . '-settings simpay-' . $key . '-tab ' . implode( ' ', $class ) . '" data-tab="' . $key . '">';
					echo apply_filters( 'simpay_admin_meta_tab_inner_html', $inner_html, $key );
					echo '</li>';
				}
			}
		}
	}

	/**
	 * Validate and save the meta box fields.
	 *
	 * @since  3.0.0
	 *
	 * @param  int      $post_id
	 * @param  \WP_Post $post
	 *
	 * @return void
	 */
	public static function save( $post_id, $post ) {

		// $post_id and $post are required.
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}

		// Don't save meta boxes for revisions or autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['simpay_meta_nonce'] ) || ! wp_verify_nonce( $_POST['simpay_meta_nonce'], 'simpay_save_data' ) ) {
			return;
		}

		// Check user has permission to edit
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		/** Payment Options */

		// Amount Type
		$amount_type = isset( $_POST['_amount_type'] ) ? esc_attr( $_POST['_amount_type'] ) : 'one_time_set';
		update_post_meta( $post_id, '_amount_type', $amount_type );

		// TODO Rewrite. Hard to read.

		// Amount
		$amount = isset( $_POST['_amount'] ) ? sanitize_text_field( $_POST['_amount'] ) : ( false !== get_post_meta( $post_id, '_amount', true ) ? get_post_meta( $post_id, '_amount', true ) : simpay_global_minimum_amount() );

		update_post_meta( $post_id, '_amount', $amount );

		/** General Options **/

		// Success Redirect Type
		$success_redirect_type = isset( $_POST['_success_redirect_type'] ) ? esc_attr( $_POST['_success_redirect_type'] ) : 'default';
		update_post_meta( $post_id, '_success_redirect_type', $success_redirect_type );

		// Success Redirect Page
		$success_redirect_page = isset( $_POST['_success_redirect_page'] ) ? esc_attr( $_POST['_success_redirect_page'] ) : '';
		update_post_meta( $post_id, '_success_redirect_page', $success_redirect_page );

		// Success Redirect URL
		$success_redirect_url = isset( $_POST['_success_redirect_url'] ) ? esc_url( $_POST['_success_redirect_url'] ) : '';
		update_post_meta( $post_id, '_success_redirect_url', $success_redirect_url );

		/** Form Display Options **/


		// Form Display Type
		$form_display_type = isset( $_POST['_form_display_type'] ) ? sanitize_text_field( $_POST['_form_display_type'] ) : '';
		update_post_meta( $post_id, '_form_display_type', $form_display_type );

		/** Stripe Checkout Display */

		// Company name
		$company_name = isset( $_POST['_company_name'] ) ? sanitize_text_field( $_POST['_company_name'] ) : '';
		update_post_meta( $post_id, '_company_name', $company_name );

		// Item Description
		$item_description = isset( $_POST['_item_description'] ) ? sanitize_text_field( $_POST['_item_description'] ) : '';
		update_post_meta( $post_id, '_item_description', $item_description );

		// Image URL
		$image_url = isset( $_POST['_image_url'] ) ? sanitize_text_field( $_POST['_image_url'] ) : '';
		update_post_meta( $post_id, '_image_url', $image_url );

		// Enable Billing Address
		$enable_billing_address = isset( $_POST['_enable_billing_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

		// Payment button style.
		if ( isset( $_POST['_payment_button_style'] ) ) {
			update_post_meta( $post_id, '_payment_button_style', $_POST['_payment_button_style'] );
		}

		// Save custom fields
		$fields = isset( $_POST['_simpay_custom_field'] ) ? $_POST['_simpay_custom_field'] : array();

		if ( ! empty( $fields ) && is_array( $fields ) ) {

			$fields = self::update_ids( $fields, $post_id );

			// Re-index the array so if fields were removed we don't overwrite the index with a new field
			foreach ( $fields as $k => $v ) {
				$fields[ $k ] = array_values( $v );
			}

			update_post_meta( $post_id, '_custom_fields', $fields );
		}

		do_action( 'simpay_save_form_settings', $post_id, $post );
	}

	/**
	 * Converts the IDs for the fields before saving
	 */
	private static function update_ids( $arr, $form_id ) {

		if ( ! empty( $arr ) && is_array( $arr ) ) {
			foreach ( $arr as $k => &$v ) {

				if ( ! empty( $v ) && is_array( $v ) ) {
					foreach ( $v as $k2 => &$v2 ) {

						if ( ! empty( $v2 ) && is_array( $v2 ) ) {
							foreach ( $v2 as $k3 => &$v3 ) {
								if ( empty ( $v3 ) ) {
									if ( 'id' === $k3 ) {

										if ( 'payment_button' !== $k ) {
											$v3 = 'simpay_' . $form_id . '_' . $k . '_' . $v2['uid'];
										} else {
											$v3 = 'simpay_' . $form_id . '_' . $k;
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $arr;
	}

	/**
	 * Output Company Info settings in the Stripe Checkout tab.
	 *
	 * @since 3.4.0
	 */
	public static function add_company_info_settings() {
		include_once SIMPLE_PAY_INC . 'core/admin/metaboxes/views/partials/company-info-settings.php';
	}

	/**
	 * Output a link back to "Custom Form Fields" under the "Checkout Button Text" field.
	 *
	 * @since 3.5.0
	 */
	public static function add_custom_form_fields_link() {
?>

<tr class="simpay-panel-field">
	<th></th>
	<td>
		<p class="description">
			<?php
			echo wp_kses_post(
				sprintf(
					__( 'Configure the on-page Payment Button in the %1$sCustom Form Fields%2$s options.', 'stripe' ),
					'<a href="#" class="simpay-tab-link" data-show-tab="simpay-form_display">',
					'</a>'
				)
			);
			?>
		</p>
	</td>
</tr>

<?php
	}
}
