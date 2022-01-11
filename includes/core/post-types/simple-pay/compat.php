<?php
/**
 * Simple Pay: Compatibility
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Compat
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Compat;

use SimplePay\Core\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects and reroutes legacy URLs.
 *
 * @since 3.8.0
 */
function redirect() {
	if ( ! isset( $_GET['page'] ) || 'simpay' !== $_GET['page'] ) {
		return;
	}

	$action = isset( $_GET['action'] )
		? sanitize_text_field( $_GET['action'] )
		: false;

	switch ( $action ) {
		// Add New.
		case 'create':
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => 'simple-pay',
					),
					admin_url( 'post-new.php' )
				)
			);

			break;
		// Edit.
		case 'edit':
			$form = isset( $_GET['form_id'] )
				? absint( $_GET['form_id'] )
				: 0;

			wp_safe_redirect(
				add_query_arg(
					array(
						'post'   => $form,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				)
			);

			break;
		// Duplicate.
		case 'duplicate':
			$form = isset( $_GET['simpay_form'] )
				? absint( $_GET['simpay_form'] )
				: 0;

			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type'     => 'simple-pay',
						'simpay-action' => 'duplicate',
						'form'          => $form,
						'_wpnonce'      => wp_create_nonce( 'simpay-duplicate-payment-form' ),
					),
					admin_url( 'edit.php' )
				)
			);

			break;
		// All Payment Forms.
		default:
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => 'simple-pay',
					),
					admin_url( 'edit.php' )
				)
			);
	}

	die();
}
add_action( 'admin_page_access_denied', __NAMESPACE__ . '\\redirect', 0 );
