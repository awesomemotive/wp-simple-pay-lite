<?php
/**
 * Admin: "Subscriptions" list view
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 *
 * @var \SimplePay\Core\Admin\ListTable\SubscriptionsListTable $list_table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Subscriptions', 'stripe' ); ?>
	</h1>

	<?php
	// Describes the mode being viewed, which is not necessarily the global
	// setting -- the mode filter can show the other one.
	if ( ! $list_table->is_viewing_livemode() ) :
		?>
		<span class="simpay-txn-test-badge"><?php esc_html_e( 'Test Mode', 'stripe' ); ?></span>
	<?php endif; ?>

	<hr class="wp-header-end">

	<form method="get">
		<input type="hidden" name="post_type" value="simple-pay" />
		<input type="hidden" name="page" value="simpay-subscriptions" />

		<?php if ( ! empty( $_GET['sub_status'] ) ) : ?>
			<input type="hidden" name="sub_status" value="<?php echo esc_attr( sanitize_text_field( $_GET['sub_status'] ) ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $_GET['orderby'] ) ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_text_field( $_GET['orderby'] ) ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $_GET['order'] ) ) : ?>
			<input type="hidden" name="order" value="<?php echo esc_attr( sanitize_text_field( $_GET['order'] ) ); ?>" />
		<?php endif; ?>

		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Search Subscriptions', 'stripe' ), 'simpay-sub-search' ); ?>
		<?php $list_table->display(); ?>
	</form>
</div>
