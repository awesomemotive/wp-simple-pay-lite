<?php
/**
 * Admin: "Subscription" detail view
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 *
 * @var \SimplePay\Core\Subscription\Subscription $subscription
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Return to the list showing this subscription's own mode.
$back_url = add_query_arg(
	array(
		'post_type'    => 'simple-pay',
		'page'         => 'simpay-subscriptions',
		'sub_livemode' => $subscription->livemode ? 'live' : 'test',
	),
	admin_url( 'edit.php' )
);

// Use per-subscription livemode for Stripe URLs (not global test mode).
$stripe_prefix = $subscription->livemode ? '' : 'test/';

$status_label = SimplePay\Core\Subscription\SubscriptionStatus::get_label( (string) $subscription->status );

// Whether the period end is a renewal, an end date, or not worth showing.
$period_end_type = SimplePay\Core\Subscription\SubscriptionStatus::get_period_end_type(
	(string) $subscription->status,
	$subscription->cancel_at_period_end
);

$period_end_label = SimplePay\Core\Subscription\SubscriptionStatus::PERIOD_END_ENDS === $period_end_type
	? __( 'Ends', 'stripe' )
	: __( 'Next Renewal', 'stripe' );

// Stripe dashboard URLs.
$subscription_url = '';
if ( ! empty( $subscription->object_id ) ) {
	$subscription_url = sprintf(
		'https://dashboard.stripe.com/%ssubscriptions/%s',
		$stripe_prefix,
		$subscription->object_id
	);
}

$customer_url = '';
if ( ! empty( $subscription->customer_id ) ) {
	$customer_url = sprintf(
		'https://dashboard.stripe.com/%scustomers/%s',
		$stripe_prefix,
		$subscription->customer_id
	);
}

// Form title.
$form_title = '';
$form_url   = '';
if ( ! empty( $subscription->form_id ) ) {
	$form_id    = absint( $subscription->form_id );
	$form_title = get_the_title( $form_id );
	$form_url   = get_edit_post_link( $form_id );

	if ( empty( $form_title ) ) {
		$form_title = sprintf( '#%d', $form_id );
	}
}

$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

// Amount + interval label.
$amount_display = simpay_format_currency( $subscription->amount, $subscription->currency );

$interval_label = '';
if ( ! empty( $subscription->billing_interval ) ) {
	$interval_count = max( 1, (int) $subscription->interval_count );
	$interval_words = array(
		'day'   => _n( 'day', 'days', $interval_count, 'stripe' ),
		'week'  => _n( 'week', 'weeks', $interval_count, 'stripe' ),
		'month' => _n( 'month', 'months', $interval_count, 'stripe' ),
		'year'  => _n( 'year', 'years', $interval_count, 'stripe' ),
	);

	$interval_word = isset( $interval_words[ $subscription->billing_interval ] )
		? $interval_words[ $subscription->billing_interval ]
		: (string) $subscription->billing_interval;

	$interval_label = 1 === $interval_count
		/* translators: %s Billing interval, e.g. "month". */
		? sprintf( __( '/ %s', 'stripe' ), $interval_word )
		: sprintf(
			/* translators: %1$d Interval count. %2$s Billing interval, e.g. "months". */
			__( '/ %1$d %2$s', 'stripe' ),
			$interval_count,
			$interval_word
		);
}
?>

<div class="wrap simpay-txn-detail">
	<a href="<?php echo esc_url( $back_url ); ?>" class="simpay-txn-detail-back">
		&larr; <?php esc_html_e( 'Back to Subscriptions', 'stripe' ); ?>
	</a>

	<h1 class="wp-heading-inline">
		<?php
		printf(
			/* translators: %d: Subscription ID */
			esc_html__( 'Subscription #%d', 'stripe' ),
			absint( $subscription->id )
		);
		?>
	</h1>

	<?php if ( ! $subscription->livemode ) : ?>
		<span class="simpay-txn-test-badge"><?php esc_html_e( 'Test Mode', 'stripe' ); ?></span>
	<?php endif; ?>

	<hr class="wp-header-end">

	<div class="simpay-txn-detail-grid">

		<!-- Left Column -->
		<div class="simpay-txn-detail-left">

			<!-- Subscription Info Card -->
			<div class="simpay-txn-card postbox">
				<div class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Subscription Info', 'stripe' ); ?></h2>
				</div>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Amount', 'stripe' ); ?></th>
								<td>
									<strong><?php echo esc_html( $amount_display ); ?></strong>
									<?php if ( '' !== $interval_label ) : ?>
										<span class="simpay-txn-interval"><?php echo esc_html( $interval_label ); ?></span>
									<?php endif; ?>
									<span class="simpay-txn-currency-badge"><?php echo esc_html( strtoupper( (string) $subscription->currency ) ); ?></span>
								</td>
							</tr>
							<?php if ( '' !== $period_end_type && ! empty( $subscription->current_period_end ) ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $period_end_label ); ?></th>
								<td><?php echo esc_html( date_i18n( $date_format, (int) $subscription->current_period_end ) ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( ! empty( $subscription->trial_end ) ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Trial Ends', 'stripe' ); ?></th>
								<td><?php echo esc_html( date_i18n( $date_format, (int) $subscription->trial_end ) ); ?></td>
							</tr>
							<?php endif; ?>
							<?php if ( ! empty( $subscription->canceled_at ) ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Canceled', 'stripe' ); ?></th>
								<td><?php echo esc_html( date_i18n( $date_format, (int) $subscription->canceled_at ) ); ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Stripe Details Card (collapsed by default) -->
			<details class="simpay-txn-card simpay-txn-card--collapsible postbox">
				<summary class="simpay-txn-card-header">
					<h2 class="hndle"><?php esc_html_e( 'Stripe Details', 'stripe' ); ?></h2>
					<span class="simpay-txn-card-toggle dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
				</summary>
				<div class="inside">
					<table class="widefat simpay-txn-table">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Subscription ID', 'stripe' ); ?></th>
								<td>
									<?php if ( ! empty( $subscription_url ) ) : ?>
										<a href="<?php echo esc_url( $subscription_url ); ?>" target="_blank" rel="noopener noreferrer">
											<?php echo esc_html( (string) $subscription->object_id ); ?> &#x2197;
										</a>
										<?php SimplePay\Core\AdminPage\CopyButton::render( $subscription->object_id ); ?>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</details>

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
						<span class="simpay-txn-status simpay-txn-status--<?php echo esc_attr( (string) $subscription->status ); ?>">
							<?php echo esc_html( $status_label ); ?>
						</span>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Created', 'stripe' ); ?></span><br>
						<?php echo ! empty( $subscription->date_created ) ? esc_html( date_i18n( $date_format, (int) $subscription->date_created ) ) : '&mdash;'; ?>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Last Updated', 'stripe' ); ?></span><br>
						<?php echo ! empty( $subscription->date_modified ) ? esc_html( date_i18n( $date_format, (int) $subscription->date_modified ) ) : '&mdash;'; ?>
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
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Customer ID', 'stripe' ); ?></span><br>
						<?php if ( ! empty( $customer_url ) ) : ?>
							<a href="<?php echo esc_url( $customer_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( (string) $subscription->customer_id ); ?> &#x2197;
							</a>
							<?php SimplePay\Core\AdminPage\CopyButton::render( $subscription->customer_id ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
					<p class="simpay-txn-meta-row">
						<span class="simpay-txn-meta-label"><?php esc_html_e( 'Email', 'stripe' ); ?></span><br>
						<?php if ( ! empty( $subscription->email ) ) : ?>
							<a href="<?php echo esc_url( sprintf( 'https://dashboard.stripe.com/%scustomers?email=%s', $stripe_prefix, rawurlencode( (string) $subscription->email ) ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html( (string) $subscription->email ); ?>
							</a>
							<?php SimplePay\Core\AdminPage\CopyButton::render( $subscription->email ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
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
						<?php if ( '' !== $form_title && ! empty( $form_url ) ) : ?>
							<a href="<?php echo esc_url( $form_url ); ?>"><?php echo esc_html( $form_title ); ?></a>
						<?php elseif ( '' !== $form_title ) : ?>
							<?php echo esc_html( $form_title ); ?>
						<?php else : ?>
							&mdash;
						<?php endif; ?>
					</p>
				</div>
			</div>

		</div><!-- .simpay-txn-detail-right -->

	</div><!-- .simpay-txn-detail-grid -->
</div>
