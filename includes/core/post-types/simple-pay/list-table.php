<?php
/**
 * Simple Pay: List table
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\List_Table
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\List_Table;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages the `simple-pay` list table row actions.
 *
 * @since 3.8.0
 *
 * @param array    $actions Payment Form actions.
 * @param \WP_Post $post    Current Payment Form \WP_Post object.
 * @return array Row actions.
 */
function row_actions( $actions, $post ) {
	if ( 'simple-pay' !== $post->post_type ) {
		return $actions;
	}

	// Remove "Quick Edit".
	unset( $actions['inline hide-if-no-js'] );

	// Preview.
	$actions = simpay_add_to_array_after(
		'simpay-preview',
		(
			'<a href="' . esc_url( get_preview_post_link( $post->ID ) ) . '" target="_blank" rel="noopener noreferrer">' .
			esc_html__( 'Preview', 'stripe' ) .
			'</a>'
		),
		'edit',
		$actions
	);

	// Duplicate.
	$duplicate_url = add_query_arg(
		array(
			'post_type'     => 'simple-pay',
			'form'          => $post->ID,
			'simpay-action' => 'duplicate',
			'_wpnonce'      => wp_create_nonce( 'simpay-duplicate-payment-form' ),
		),
		admin_url( 'edit.php' )
	);

	$actions = simpay_add_to_array_after(
		'simpay-duplicate',
		(
			'<a href="' . esc_url( $duplicate_url ) . '">' .
			esc_html__( 'Duplicate', 'stripe' ) .
			'</a>'
		),
		'simpay-preview',
		$actions
	);

	return $actions;
}
add_filter( 'post_row_actions', __NAMESPACE__ . '\\row_actions', 10, 2 );

/**
 * Adds a "Payment Mode" column to the `simple-pay` list table.
 *
 * @since 3.9.0
 *
 * @param array $columns List table columns.
 * @return array Modified list of columns.
 */
function add_livemode_column( $columns ) {
	$columns = simpay_add_to_array_after(
		'livemode',
		'<span class="screen-reader-text">' . esc_html__( 'Payment Mode', 'stripe' ) . '</span>',
		'title',
		$columns
	);

	return $columns;
}
add_filter( 'manage_edit-simple-pay_columns', __NAMESPACE__ . '\\add_livemode_column' );

/**
 * Adds a "Shortcode" column to the `simple-pay` list table.
 *
 * @since 3.8.0
 *
 * @param array $columns List table columns.
 * @return array Modified list of columns.
 */
function add_shortcode_column( $columns ) {
	$columns = simpay_add_to_array_after(
		'shortcode',
		esc_html__( 'Shortcode', 'stripe' ),
		'livemode',
		$columns
	);

	return $columns;
}
add_filter( 'manage_edit-simple-pay_columns', __NAMESPACE__ . '\\add_shortcode_column' );

/**
 * Outputs the "Shortcode" column content in the `simple-pay` list table.
 *
 * @since 3.8.0
 *
 * @param string $column  Column key.
 * @param int    $post_id Current Payment Form \WP_Post ID.
 */
function output_shortcode_column( $column, $post_id ) {
	if ( 'shortcode' !== $column ) {
		return;
	}
	?>

<div id="simpay-get-shortcode">
	<?php simpay_print_shortcode_tip( $post_id ); ?>
</div>

	<?php
}
add_action( 'manage_simple-pay_posts_custom_column', __NAMESPACE__ . '\\output_shortcode_column', 10, 2 );

/**
 * Outputs the "Payment Mode" column content in the `simple-pay` list table.
 *
 * @since 3.9.0
 *
 * @param string $column  Column key.
 * @param int    $post_id Current Payment Form \WP_Post ID.
 */
function output_livemode_column( $column, $post_id ) {
	if ( 'livemode' !== $column ) {
		return;
	}

	$livemode          = simpay_get_saved_meta( $post_id, '_livemode', '' );
	$livemode_filtered = simpay_get_filtered( 'livemode', $livemode, $post_id );
	?>

	<?php if ( '1' === $livemode_filtered ) : ?>
		<div class="simpay-badge simpay-badge--green">
			<?php esc_html_e( 'Live Mode', 'stripe' ); ?>
		</div>
	<?php elseif ( '0' === $livemode_filtered ) : ?>
		<div class="simpay-badge simpay-badge--yellow">
			<?php esc_html_e( 'Test Mode', 'stripe' ); ?>
		</div>
	<?php endif; ?>

	<?php
}
add_action( 'manage_simple-pay_posts_custom_column', __NAMESPACE__ . '\\output_livemode_column', 10, 2 );
