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

					$payment_options_template = apply_filters( 'simpay_payment_options_template', 'views/tabs/tab-payment-options.php' );

					include_once( $payment_options_template );

					do_action( 'simpay_form_settings_meta_payment_options_panel', $post->ID );
					?>
				</div>

				<!-- On-Page Form Display Options Tab -->
				<div id="form-display-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$form_display_template = apply_filters( 'simpay_form_display_template', 'views/tabs/tab-form-display.php' );

					include_once( $form_display_template );

					do_action( 'simpay_form_settings_meta_form_display_panel', $post->ID );
					?>
				</div>

				<!-- Checkout Overlay Display Options Tab -->
				<div id="overlay-display-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$overlay_display_template = apply_filters( 'simpay_overlay_display_template', 'views/tabs/tab-overlay-display.php' );

					include_once( $overlay_display_template );

					do_action( 'simpay_form_settings_meta_overlay_display_panel', $post->ID );
					?>
				</div>

				<!-- Subscription Options Tab -->
				<div id="subscription-options-settings-panel" class="simpay-panel simpay-panel-hidden">
					<?php

					$subscription_options_template = apply_filters( 'simpay_subscription_options_template', SIMPLE_PAY_INC . 'core/admin/metaboxes/views/tabs/tab-subscription-options.php' );

					include_once( $subscription_options_template );

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
				'target' => 'form-display-settings-panel',
				'class'  => array(),
				'icon'   => '',
			),
			'overlay_display'      => array(
				'label'  => esc_html__( 'Checkout Overlay Display', 'stripe' ),
				'target' => 'overlay-display-settings-panel',
				'class'  => array(),
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

		// See what type of currency we are dealing with so we know how to save the values
		$is_zero_decimal = simpay_is_zero_decimal();


		/** Payment Options */

		// Amount Type
		$amount_type = isset( $_POST['_amount_type'] ) ? esc_attr( $_POST['_amount_type'] ) : 'one_time_set';
		update_post_meta( $post_id, '_amount_type', $amount_type );

		// Amount
		if ( $is_zero_decimal ) {
			$amount = isset( $_POST['_amount'] ) ? sanitize_text_field( $_POST['_amount'] ) : ( false !== get_post_meta( $post_id, '_amount', true ) ? get_post_meta( $post_id, '_amount', true ) : '100' );
		} else {
			$amount = isset( $_POST['_amount'] ) ? sanitize_text_field( $_POST['_amount'] ) : ( false !== get_post_meta( $post_id, '_amount', true ) ? get_post_meta( $post_id, '_amount', true ) : '1' );
		}
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

		// Verify Zip/Postal Code
		$verify_zip = isset( $_POST['_verify_zip'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_verify_zip', $verify_zip );

		/** Checkout Overlay Display */

		// Company name
		$company_name = isset( $_POST['_company_name'] ) ? sanitize_text_field( $_POST['_company_name'] ) : '';
		update_post_meta( $post_id, '_company_name', $company_name );

		// Image URL
		$image_url = isset( $_POST['_image_url'] ) ? sanitize_text_field( $_POST['_image_url'] ) : '';
		update_post_meta( $post_id, '_image_url', $image_url );

		// Item Description
		$item_description = isset( $_POST['_item_description'] ) ? sanitize_text_field( $_POST['_item_description'] ) : '';
		update_post_meta( $post_id, '_item_description', $item_description );

		// Enable Remember Me
		$enable_remember_me = isset( $_POST['_enable_remember_me'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_remember_me', $enable_remember_me );

		// Enable Billing Address
		$enable_billing_address = isset( $_POST['_enable_billing_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

		// Enable Shipping Address
		$enable_shipping_address = isset( $_POST['_enable_shipping_address'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_shipping_address', $enable_shipping_address );

		// Checkout Button Text
		$checkout_button_text = isset( $_POST['_checkout_button_text'] ) ? sanitize_text_field( $_POST['_checkout_button_text'] ) : '';
		update_post_meta( $post_id, '_checkout_button_text', $checkout_button_text );

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
}
