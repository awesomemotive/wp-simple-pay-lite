<?php
/**
 * Simple Pay: Actions
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Actions
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Actions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Saves the Payment Form's settings.
 *
 * @since 3.8.0
 *
 * @param int      $post_id Current Payment Form ID.
 * @param \WP_Post $post    Current Payment Form \WP_Post object.
 * @param bool     $update  Whether this is an existing Payment Form or update.
 */
function save( $post_id, $post, $update ) {
	// Bail if we have no Payment Form.
	if ( empty( $post_id ) || empty( $post ) ) {
		return;
	}

	// Bail if doing an autosave or is a revision.
	if (
		defined( 'DOING_AUTOSAVE' ) ||
		is_int( wp_is_post_revision( $post ) ) ||
		is_int( wp_is_post_autosave( $post ) )
	) {
		return;
	}

	// Bail if we cannot verify our nonce.
	if (
		empty( $_POST['simpay_meta_nonce'] ) ||
		! wp_verify_nonce( $_POST['simpay_meta_nonce'], 'simpay_save_data' )
	) {
		return;
	}

	// Bail if the current user does not have permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Payment Mode.
	$livemode = isset( $_POST['_livemode'] ) && '' !== $_POST['_livemode']
		? absint( $_POST['_livemode'] )
		: '';

	if ( '' !== $livemode ) {
		update_post_meta(
			$post_id,
			'_livemode_prev',
			get_post_meta( $post_id, '_livemode', true )
		);

		update_post_meta( $post_id, '_livemode', $livemode );
	} else {
		delete_post_meta( $post_id, '_livemode' );
		delete_post_meta( $post_id, '_livemode_prev' );
	}

	// Amount.
	$amount = isset( $_POST['_amount'] )
		? sanitize_text_field( $_POST['_amount'] )
		: (
				false !== get_post_meta( $post_id, '_amount', true )
					? get_post_meta( $post_id, '_amount', true )
					: simpay_global_minimum_amount()
			);

	update_post_meta( $post_id, '_amount', $amount );

	// Success redirect type.
	$success_redirect_type = isset( $_POST['_success_redirect_type'] )
		? esc_attr( $_POST['_success_redirect_type'] )
		: 'default';

	update_post_meta( $post_id, '_success_redirect_type', $success_redirect_type );

	// Success redirect page.
	$success_redirect_page = isset( $_POST['_success_redirect_page'] )
		? esc_attr( $_POST['_success_redirect_page'] )
		: '';

	update_post_meta( $post_id, '_success_redirect_page', $success_redirect_page );

	// Success redirect URL.
	$success_redirect_url = isset( $_POST['_success_redirect_url'] )
		? esc_url( $_POST['_success_redirect_url'] )
		: '';

	update_post_meta( $post_id, '_success_redirect_url', $success_redirect_url );

	// Company name.
	$company_name = isset( $_POST['_company_name'] )
		? sanitize_text_field( $_POST['_company_name'] )
		: '';

	update_post_meta( $post_id, '_company_name', $company_name );

	// Item description.
	$item_description = isset( $_POST['_item_description'] )
		? sanitize_text_field( $_POST['_item_description'] )
		: '';

	update_post_meta( $post_id, '_item_description', $item_description );

	// Image URL.
	$image_url = isset( $_POST['_image_url'] )
		? sanitize_text_field( $_POST['_image_url'] )
		: '';

	update_post_meta( $post_id, '_image_url', $image_url );

	// Submit type.
	$checkout_submit_type = isset( $_POST['_checkout_submit_type'] )
		? sanitize_text_field( $_POST['_checkout_submit_type'] )
		: 'pay';

	update_post_meta( $post_id, '_checkout_submit_type', $checkout_submit_type );

	// Billing address.
	$enable_billing_address = isset( $_POST['_enable_billing_address'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_billing_address', $enable_billing_address );

	// Shipping address.
	$enable_shipping_address = isset( $_POST['_enable_shipping_address'] )
		? 'yes'
		: 'no';

	update_post_meta( $post_id, '_enable_shipping_address', $enable_shipping_address );

	// Custom fields.
	// Handles "Payment Button Text" and "Payment Button Processing Text".
	$fields = isset( $_POST['_simpay_custom_field'] )
		? $_POST['_simpay_custom_field']
		: array();

	update_post_meta( $post_id, '_custom_fields', $fields );

	if ( false === $update ) {
		/**
		 * Allows further action to be taken when a Payment Form is created.
		 *
		 * @since 3.0.0
		 *
		 * @param int $post_id Payment Form ID.
		 */
		do_action( 'simpay_form_created', $post_id );
	}

	/**
	 * Allows further action to be taken when a Payment Form is updated.
	 *
	 * @since 3.0.0
	 *
	 * @param int      $post_id Payment Form ID.
	 * @param \WP_Post $post    Payment Form \WP_Post object.
	 */
	do_action( 'simpay_save_form_settings', $post_id, $post );
}
add_action( 'save_post_simple-pay', __NAMESPACE__ . '\\save', 10, 3 );

/**
 * Redirects the Payment Form back to the current Payment Form settings tab.
 *
 * @since 3.8.0
 *
 * @param string $location Location to redirect to.
 * @param int    $post_id  Payment Form ID.
 * @return string URL to redirect to.
 */
function save_redirect( $location, $post_id ) {
	$post = get_post( $post_id );

	if ( 'simple-pay' !== $post->post_type ) {
		return $location;
	}

	$hash = isset( $_REQUEST['simpay_form_settings_tab'] )
		? sanitize_text_field( $_REQUEST['simpay_form_settings_tab'] )
		: '#payment-options-settings-panel';

	$location .= $hash;

	return $location;
}
add_filter( 'redirect_post_location', __NAMESPACE__ . '\\save_redirect', 10, 2 );

/**
 * Duplicates an existing Payment Form.
 *
 * @since 3.8.0
 */
function duplicate() {
	// Bail if no post is found.
	if ( ! isset( $_REQUEST['form'] ) ) {
		return;
	}

	// Bail if no action is found, or not duplicating.
	if ( ! isset( $_REQUEST['simpay-action'] ) || 'duplicate' !== $_REQUEST['simpay-action'] ) {
		return;
	}

	// Bail if the request is invalid.
	check_admin_referer( 'simpay-duplicate-payment-form' );

	$post = get_post( absint( $_GET['form'] ) );

	// Bail if post cannot be found.
	if ( empty( $post ) ) {
		return;
	}

	// Insert the new post using the original post values plus some modifications.
	$duplicate = wp_insert_post(
		array(
			'post_author' => wp_get_current_user()->ID,
			'post_title'  => $post->post_title . __( ' - Duplicate', 'stripe' ),
			'post_type'   => $post->post_type,
			'post_status' => $post->post_status,
		)
	);

	// If the new post did not get inserted then exit now.
	if ( ! $duplicate ) {
		return;
	}

	// Duplicate metadata.
	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"INSERT INTO {$wpdb->prefix}postmeta (post_id, meta_key, meta_value) SELECT %d, meta_key, meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d",
			$duplicate,
			$post->ID
		)
	);

	$redirect = add_query_arg(
		array(
			'post'    => $duplicate,
			'action'  => 'edit',
			'message' => 299,
		),
		admin_url( 'post.php' )
	);

	wp_safe_redirect( $redirect );
}
add_action( 'admin_init', __NAMESPACE__ . '\\duplicate' );
