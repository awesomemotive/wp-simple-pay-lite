<?php
/**
 * Admin: "Transaction" detail view
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 *
 * @var object{
 *     id: string,
 *     form_id: string,
 *     object: string,
 *     _object_id: string,
 *     livemode: string,
 *     amount_total: string,
 *     amount_subtotal: string,
 *     amount_shipping: string,
 *     amount_discount: string,
 *     amount_tax: string,
 *     amount_refunded: string,
 *     currency: string,
 *     email: string,
 *     customer_id: string,
 *     subscription_id: string|null,
 *     status: string,
 *     application_fee: string|null,
 *     ip_address: string,
 *     date_created: string,
 *     date_modified: string,
 *     uuid: string,
 *     payment_method_type: string
 * } $transaction
 * @var bool $is_refundable
 * @var bool $can_refund
 * @var array<int, array{label: string, url: string, filename: string}> $file_uploads
 * @var array<int, array{label: string, value: string}> $custom_fields
 * @var array<int, array{key: string, value: string}> $metadata_rows
 * @var string $payment_method
 * @var string $receipt_url
 * @var string $customer_name
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Return to the list showing this transaction's own mode, which is not
// necessarily the global one -- a payment form can override it.
$back_url = add_query_arg(
	array(
		'post_type'    => 'simple-pay',
		'page'         => 'simpay-transactions',
		'txn_livemode' => $transaction->livemode ? 'live' : 'test',
	),
	admin_url( 'edit.php' )
);

// Use per-transaction livemode for Stripe URLs (not global test mode).
$stripe_prefix = $transaction->livemode ? '' : 'test/';

// Determine display status (partial refund detection).
$display_status = $transaction->status;
if (
	'refunded' === $transaction->status &&
	absint( $transaction->amount_refunded ) > 0 &&
	absint( $transaction->amount_refunded ) < absint( $transaction->amount_total )
) {
	$display_status = 'partial_refund';
}

$status_labels = array(
	'succeeded'               => __( 'Succeeded', 'stripe' ),
	'failed'                  => __( 'Failed', 'stripe' ),
	'refunded'                => __( 'Refunded', 'stripe' ),
	'partial_refund'          => __( 'Partially Refunded', 'stripe' ),
	'canceled'                => __( 'Canceled', 'stripe' ),
	'requires_payment_method' => __( 'Incomplete', 'stripe' ),
	'requires_confirmation'   => __( 'Incomplete', 'stripe' ),
	'requires_action'         => __( 'Incomplete', 'stripe' ),
	'processing'              => __( 'Processing', 'stripe' ),
);

$status_label = isset( $status_labels[ $display_status ] )
	? $status_labels[ $display_status ]
	: ucfirst( $display_status );

// Build customer URL.
$customer_url = '';
if ( ! empty( $transaction->customer_id ) ) {
	$customer_url = sprintf(
		'https://dashboard.stripe.com/%scustomers/%s',
		$stripe_prefix,
		$transaction->customer_id
	);
}

// Build subscription URL.
$subscription_url = '';
if ( ! empty( $transaction->subscription_id ) ) {
	$subscription_url = sprintf(
		'https://dashboard.stripe.com/%ssubscriptions/%s',
		$stripe_prefix,
		$transaction->subscription_id
	);
}

// Form title.
$form_title = '';
$form_url   = '';
if ( ! empty( $transaction->form_id ) ) {
	$form_id    = absint( $transaction->form_id );
	$form_title = get_the_title( $form_id );
	$form_url   = get_edit_post_link( $form_id );

	if ( empty( $form_title ) ) {
		$form_title = sprintf( '#%d', $form_id );
	}
}

$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

// Refund display values.
$is_zero_decimal        = simpay_is_zero_decimal( $transaction->currency );
$max_refundable_raw     = absint( $transaction->amount_total ) - absint( $transaction->amount_refunded );
$max_refundable_display = $is_zero_decimal ? $max_refundable_raw : round( $max_refundable_raw / 100, 2 );
$step_value             = $is_zero_decimal ? '1' : '0.01';
?>

<div class="wrap simpay-txn-detail">
	<a href="<?php echo esc_url( $back_url ); ?>" class="simpay-txn-detail-back">
		&larr; <?php esc_html_e( 'Back to Transactions', 'stripe' ); ?>
	</a>

	<h1 class="wp-heading-inline">
		<?php
		printf(
			/* translators: %d: Transaction ID */
			esc_html__( 'Transaction #%d', 'stripe' ),
			absint( $transaction->id )
		);
		?>
	</h1>

	<?php if ( ! $transaction->livemode ) : ?>
		<span class="simpay-txn-test-badge"><?php esc_html_e( 'Test Mode', 'stripe' ); ?></span>
	<?php endif; ?>

	<hr class="wp-header-end">

	<div class="simpay-txn-detail-grid">

		<!-- Left Column -->
		<div class="simpay-txn-detail-left">

			<!-- Payment Info Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Payment Info', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Amount Total', 'stripe' ); ?></th>
								<td>
									<strong><?php echo esc_html( simpay_format_currency( $transaction->amount_total, $transaction->currency ) ); ?></strong>
									<span class="simpay-txn-currency-badge"><?php echo esc_html( strtoupper( $transaction->currency ) ); ?></span>
								</td>
							</tr>
							<?php if ( absint( $transaction->amount_subtotal ) !== absint( $transaction->amount_total ) ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Subtotal', 'stripe' ); ?></th>
								<td><?php echo esc_html( simpay_format_currency( $transaction->amount_subtotal, $transaction->currency ) ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( absint( $transaction->amount_tax ) > 0 ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Tax', 'stripe' ); ?></th>
								<td><?php echo esc_html( simpay_format_currency( $transaction->amount_tax, $transaction->currency ) ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( absint( $transaction->amount_shipping ) > 0 ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Shipping', 'stripe' ); ?></th>
								<td><?php echo esc_html( simpay_format_currency( $transaction->amount_shipping, $transaction->currency ) ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( absint( $transaction->amount_discount ) > 0 ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Discount', 'stripe' ); ?></th>
								<td>
									&minus;<?php echo esc_html( simpay_format_currency( $transaction->amount_discount, $transaction->currency ) ); ?>
								</td>
							</tr>
							<?php endif; ?>
							<?php if ( absint( $transaction->amount_refunded ) > 0 ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Refunded', 'stripe' ); ?></th>
								<td>
									<span class="simpay-txn-refunded-amount">
										&minus;<?php echo esc_html( simpay_format_currency( $transaction->amount_refunded, $transaction->currency ) ); ?>
									</span>
								</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Stripe Details Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Stripe Details', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<?php if ( ! empty( $transaction->subscription_id ) ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Subscription', 'stripe' ); ?></th>
								<td>
									<?php if ( ! empty( $subscription_url ) ) : ?>
										<a href="<?php echo esc_url( $subscription_url ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( $transaction->subscription_id ); ?> &#x2197;
										</a>
										<?php \SimplePay\Core\AdminPage\CopyButton::render( $transaction->subscription_id ); ?>
									<?php else : ?>
										<?php echo esc_html( $transaction->subscription_id ); ?>
										<?php \SimplePay\Core\AdminPage\CopyButton::render( $transaction->subscription_id ); ?>
									<?php endif; ?>
								</td>
							</tr>
							<?php endif; ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Payment Method', 'stripe' ); ?></th>
								<td>
									<?php if ( ! empty( $payment_method ) ) : ?>
										<?php echo esc_html( $payment_method ); ?>
									<?php elseif ( ! empty( $transaction->payment_method_type ) ) : ?>
										<?php echo esc_html( ucfirst( str_replace( '_', ' ', $transaction->payment_method_type ) ) ); ?>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
							</tr>
							<?php if ( ! empty( $receipt_url ) ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Receipt', 'stripe' ); ?></th>
								<td>
									<a href="<?php echo esc_url( $receipt_url ); ?>" target="_blank" rel="noopener noreferrer">
										<?php esc_html_e( 'View receipt', 'stripe' ); ?> &#x2197;
									</a>
								</td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Uploaded Files Card -->
			<?php if ( ! empty( $file_uploads ) ) : ?>
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Uploaded Files', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<?php foreach ( $file_uploads as $file_upload ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $file_upload['label'] ); ?></th>
								<td>
									<a href="<?php echo esc_url( $file_upload['url'] ); ?>" target="_blank" rel="noopener noreferrer">
										<?php echo esc_html( $file_upload['filename'] ); ?> &#x2197;
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>

			<!-- Custom Fields Card -->
			<?php if ( ! empty( $custom_fields ) ) : ?>
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Custom Fields', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<?php foreach ( $custom_fields as $custom_field ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $custom_field['label'] ); ?></th>
								<td><?php echo esc_html( $custom_field['value'] ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>

			<!-- Metadata Card (collapsed by default) -->
			<?php if ( ! empty( $metadata_rows ) ) : ?>
			<details class="simpay-txn-card simpay-txn-card--collapsible postbox">
				<summary class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Metadata', 'stripe' ); ?></h2>
					<span class="simpay-txn-card-toggle dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
				</summary>
				<div class="inside">
					<table class="widefat simpay-txn-table simpay-txn-table--metadata">
						<tbody>
							<?php foreach ( $metadata_rows as $metadata_row ) : ?>
							<tr>
								<th scope="row"><code><?php echo esc_html( $metadata_row['key'] ); ?></code></th>
								<td><?php echo esc_html( $metadata_row['value'] ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</details>
			<?php endif; ?>

			<!-- Actions Card (only if refundable) -->
			<?php if ( $is_refundable ) : ?>
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Actions', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<?php if ( $can_refund ) : ?>
						<p class="simpay-txn-actions-description">
							<?php
							printf(
								/* translators: %s: formatted remaining refundable amount */
								esc_html__( 'Remaining refundable amount: %s', 'stripe' ),
								'<strong>' . esc_html( simpay_format_currency( $max_refundable_raw, $transaction->currency ) ) . '</strong>'
							);
							?>
						</p>
						<button type="button" class="button button-secondary" id="simpay-open-refund-modal">
							<?php esc_html_e( 'Refund Payment', 'stripe' ); ?>
						</button>
					<?php else : ?>
						<p class="simpay-txn-actions-description">
							<?php esc_html_e( 'A Professional license or higher is required to process refunds from this page.', 'stripe' ); ?>
						</p>
						<a href="<?php echo esc_url( simpay_pro_upgrade_url( 'transactions-refund' ) ); ?>" class="button button-secondary" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'Upgrade to Professional', 'stripe' ); ?> &#x2197;
						</a>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

		</div><!-- .simpay-txn-detail-left -->

		<!-- Right Column -->
		<div class="simpay-txn-detail-right">

			<!-- Status Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Status', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<p>
						<span class="simpay-txn-status simpay-txn-status--<?php echo esc_attr( $display_status ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Created', 'stripe' ); ?></span><br>
						<?php echo esc_html( date_i18n( $date_format, strtotime( $transaction->date_created ) ) ); ?>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Last Updated', 'stripe' ); ?></span><br>
						<?php echo esc_html( date_i18n( $date_format, strtotime( $transaction->date_modified ) ) ); ?>
					</p>
				</div>
			</div>

			<!-- Customer Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Customer', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Name', 'stripe' ); ?></span><br>
						<?php echo ! empty( $customer_name ) ? esc_html( $customer_name ) : '&mdash;'; ?>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Customer ID', 'stripe' ); ?></span><br>
						<?php if ( ! empty( $customer_url ) ) : ?>
							<a href="<?php echo esc_url( $customer_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $transaction->customer_id ); ?> &#x2197;
							</a>
							<?php \SimplePay\Core\AdminPage\CopyButton::render( $transaction->customer_id ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Email', 'stripe' ); ?></span><br>
						<?php if ( ! empty( $transaction->email ) ) : ?>
							<a href="<?php echo esc_url( sprintf( 'https://dashboard.stripe.com/%scustomers?email=%s', $stripe_prefix, rawurlencode( $transaction->email ) ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( $transaction->email ); ?>
							</a>
							<?php \SimplePay\Core\AdminPage\CopyButton::render( $transaction->email ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
					<?php if ( ! empty( $transaction->ip_address ) ) : ?>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'IP Address', 'stripe' ); ?></span><br>
						<?php
						// Let long IPv6 addresses wrap at a colon, not mid-group.
						echo wp_kses(
							str_replace( ':', ':<wbr>', esc_html( $transaction->ip_address ) ),
							array( 'wbr' => array() )
						);
						?>
					</p>
					<?php endif; ?>
				</div>
			</div>

			<!-- Form Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Form', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Payment Form', 'stripe' ); ?></span><br>
						<?php if ( ! empty( $form_url ) ) : ?>
							<a href="<?php echo esc_url( $form_url ); ?>">
								<?php echo esc_html( $form_title ); ?>
							</a>
						<?php elseif ( ! empty( $form_title ) ) : ?>
							<?php echo esc_html( $form_title ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
				</div>
			</div>

		</div><!-- .simpay-txn-detail-right -->

	</div><!-- .simpay-txn-detail-grid -->

	<?php if ( $can_refund ) : ?>
	<!-- Refund Modal -->
	<div class="simpay-refund-modal" id="simpay-refund-modal" style="display: none;">
		<div class="simpay-refund-modal-backdrop"></div>
		<div class="simpay-refund-modal-dialog">
			<div class="simpay-refund-modal-header">
				<h3><?php esc_html_e( 'Refund Payment', 'stripe' ); ?></h3>
				<button type="button" class="simpay-refund-modal-close" aria-label="<?php esc_attr_e( 'Close', 'stripe' ); ?>">&times;</button>
			</div>
			<div class="simpay-refund-modal-body">
				<div class="simpay-refund-modal-message" id="simpay-refund-message" style="display: none;"></div>

				<fieldset id="simpay-refund-fieldset">
					<p>
						<label>
							<input type="radio" name="simpay_refund_type" value="full" checked>
							<?php
							printf(
								/* translators: %s: formatted refundable amount */
								esc_html__( 'Full refund (%s)', 'stripe' ),
								esc_html( simpay_format_currency( $max_refundable_raw, $transaction->currency ) )
							);
							?>
						</label>
					</p>
					<p>
						<label>
							<input type="radio" name="simpay_refund_type" value="partial">
							<?php esc_html_e( 'Partial refund', 'stripe' ); ?>
						</label>
					</p>

					<div class="simpay-refund-partial-amount" id="simpay-refund-partial-wrap" style="display: none;">
						<label for="simpay-refund-amount">
							<?php
							printf(
								/* translators: %s: currency code */
								esc_html__( 'Amount (%s)', 'stripe' ),
								esc_html( strtoupper( $transaction->currency ) )
							);
							?>
						</label>
						<input
							type="number"
							id="simpay-refund-amount"
							step="<?php echo esc_attr( $step_value ); ?>"
							min="<?php echo esc_attr( $step_value ); ?>"
							max="<?php echo esc_attr( (string) $max_refundable_display ); ?>"
							placeholder="0.00"
						>
						<p class="description">
							<?php
							printf(
								/* translators: %s: formatted max refundable amount */
								esc_html__( 'Maximum: %s', 'stripe' ),
								esc_html( simpay_format_currency( $max_refundable_raw, $transaction->currency ) )
							);
							?>
						</p>
					</div>
				</fieldset>
			</div>
			<div class="simpay-refund-modal-footer">
				<button type="button" class="button button-secondary" id="simpay-refund-cancel">
					<?php esc_html_e( 'Cancel', 'stripe' ); ?>
				</button>
				<button type="button" class="button button-primary" id="simpay-refund-submit">
					<?php esc_html_e( 'Process Refund', 'stripe' ); ?>
				</button>
			</div>
		</div>
	</div>
	<?php endif; ?>

</div>
