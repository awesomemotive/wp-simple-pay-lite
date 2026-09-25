<?php
/**
 * Admin: "Transactions" list view
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 *
 * @var \SimplePay\Core\Admin\ListTable\TransactionsListTable $list_table
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php esc_html_e( 'Transactions', 'stripe' ); ?>
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
		<input type="hidden" name="page" value="simpay-transactions" />

		<?php if ( ! empty( $_GET['txn_status'] ) ) : ?>
			<input type="hidden" name="txn_status" value="<?php echo esc_attr( sanitize_text_field( $_GET['txn_status'] ) ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $_GET['orderby'] ) ) : ?>
			<input type="hidden" name="orderby" value="<?php echo esc_attr( sanitize_text_field( $_GET['orderby'] ) ); ?>" />
		<?php endif; ?>

		<?php if ( ! empty( $_GET['order'] ) ) : ?>
			<input type="hidden" name="order" value="<?php echo esc_attr( sanitize_text_field( $_GET['order'] ) ); ?>" />
		<?php endif; ?>

		<?php $list_table->views(); ?>
		<?php $list_table->search_box( __( 'Search Transactions', 'stripe' ), 'simpay-txn-search' ); ?>
		<?php $list_table->display(); ?>
	</form>
</div>
