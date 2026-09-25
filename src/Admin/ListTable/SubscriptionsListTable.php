<?php
/**
 * List Table: Subscriptions
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Admin\ListTable;

use SimplePay\Core\Subscription\SubscriptionStatus;
use SimplePay\Core\Utils\AccountScope;
use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * SubscriptionsListTable class.
 *
 * @since 4.17.4
 *
 * @phpstan-type Subscription_Row object{
 *     id: string,
 *     form_id: string,
 *     _object_id: string,
 *     customer_id: string,
 *     email: string,
 *     livemode: string,
 *     status: string,
 *     amount: string,
 *     currency: string,
 *     billing_interval: string|null,
 *     interval_count: string,
 *     current_period_end: string|null,
 *     cancel_at_period_end: string,
 *     canceled_at: string|null,
 *     trial_end: string|null,
 *     application_fee: string,
 *     date_created: string,
 *     date_modified: string,
 *     uuid: string
 * }
 */
class SubscriptionsListTable extends WP_List_Table {

	/**
	 * Payment form ID => display label, or null until resolved.
	 *
	 * @since 4.17.4
	 *
	 * @var array<int, string>|null
	 */
	private $form_options = null;

	/**
	 * Constructor.
	 *
	 * @since 4.17.4
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'subscription',
				'plural'   => 'subscriptions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Returns the list of columns.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	public function get_columns() {
		return array(
			'id'                 => __( 'ID', 'stripe' ),
			'date_created'       => __( 'Date', 'stripe' ),
			'email'              => __( 'Customer', 'stripe' ),
			'form_id'            => __( 'Form', 'stripe' ),
			'amount'             => __( 'Amount', 'stripe' ),
			'status'             => __( 'Status', 'stripe' ),
			'current_period_end' => __( 'Next Renewal', 'stripe' ),
		);
	}

	/**
	 * Returns the list of sortable columns.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, array<string|bool>>
	 */
	protected function get_sortable_columns() {
		return array(
			'id'                 => array( 'id', true ),
			'date_created'       => array( 'date_created', false ),
			'amount'             => array( 'amount', false ),
			'current_period_end' => array( 'current_period_end', false ),
		);
	}

	/**
	 * Returns the payment form the list is currently filtered by.
	 *
	 * The query variable is `sub_form_id`, mirroring the Transactions list's
	 * `txn_form_id`, to avoid the bare `form_id` claimed by the payment
	 * confirmation redirect.
	 *
	 * @since 4.17.4
	 *
	 * @return int
	 */
	private function get_current_form_id() {
		return isset( $_GET['sub_form_id'] ) ? absint( $_GET['sub_form_id'] ) : 0;
	}

	/**
	 * Returns the payment mode the list is currently showing.
	 *
	 * @since 4.17.4
	 *
	 * @return string Either 'live' or 'test'.
	 */
	private function get_current_mode() {
		$requested = isset( $_GET['sub_livemode'] )
			? sanitize_text_field( $_GET['sub_livemode'] )
			: '';

		if ( in_array( $requested, array( 'live', 'test' ), true ) ) {
			return $requested;
		}

		return simpay_is_test_mode() ? 'test' : 'live';
	}

	/**
	 * Returns the payment mode as stored in the `livemode` column.
	 *
	 * @since 4.17.4
	 *
	 * @return int Either 1 (live) or 0 (test).
	 */
	private function get_current_livemode() {
		return 'live' === $this->get_current_mode() ? 1 : 0;
	}

	/**
	 * Determines whether the list is currently showing live subscriptions.
	 *
	 * @since 4.17.4
	 *
	 * @return bool
	 */
	public function is_viewing_livemode() {
		return 'live' === $this->get_current_mode();
	}

	/**
	 * Returns the status the list is currently filtered by.
	 *
	 * @since 4.17.4
	 *
	 * @return string
	 */
	private function get_current_status() {
		$status = isset( $_GET['sub_status'] )
			? sanitize_text_field( $_GET['sub_status'] )
			: '';

		return array_key_exists( $status, $this->get_status_views() )
			? $status
			: '';
	}

	/**
	 * Returns the current search term.
	 *
	 * @since 4.17.4
	 *
	 * @return string
	 */
	private function get_current_search() {
		return isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
	}

	/**
	 * Returns the status filter views as an ordered map of view key => label.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	private function get_status_views() {
		$views = array( '' => __( 'All', 'stripe' ) );

		foreach ( SubscriptionStatus::get_views() as $view_key => $view ) {
			$views[ $view_key ] = $view['label'];
		}

		return $views;
	}

	/**
	 * Returns the `AND ...` WHERE fragment for a status view key.
	 *
	 * A view can cover more than one stored status (e.g. "Incomplete" also
	 * matches `incomplete_expired`).
	 *
	 * @since 4.17.4
	 *
	 * @param string $status_key Status view key.
	 * @return string
	 */
	private function get_status_where_fragment( $status_key ) {
		global $wpdb;

		$views    = SubscriptionStatus::get_views();
		$statuses = isset( $views[ $status_key ] )
			? $views[ $status_key ]['statuses']
			: array( $status_key );

		$placeholders = implode( ', ', array_fill( 0, count( $statuses ), '%s' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is a list of %s placeholders.
		return $wpdb->prepare( " AND status IN ( {$placeholders} )", $statuses );
	}

	/**
	 * Returns the status filter views.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	protected function get_views() {
		global $wpdb;

		$current_status = $this->get_current_status();
		$current_form   = $this->get_current_form_id();
		$current_search = $this->get_current_search();

		$base_args = array(
			'post_type'    => 'simple-pay',
			'page'         => 'simpay-subscriptions',
			'sub_livemode' => $this->get_current_mode(),
		);

		if ( $current_form > 0 ) {
			$base_args['sub_form_id'] = $current_form;
		}

		if ( '' !== $current_search ) {
			$base_args['s'] = $current_search;
		}

		$base_url = add_query_arg( $base_args, admin_url( 'edit.php' ) );

		// Counts reflect every active filter except the status itself.
		$where = $this->get_where_clause( false );

		// One grouped query for every status, rather than one per view.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is built from $wpdb->prepare() fragments.
		$rows = $wpdb->get_results(
			"SELECT status, COUNT(*) AS total
			FROM {$wpdb->prefix}wpsp_subscriptions {$where}
			GROUP BY status"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$by_status = array();

		foreach ( (array) $rows as $count_row ) {
			$by_status[ (string) $count_row->status ] = (int) $count_row->total;
		}

		$statuses = $this->get_status_views();
		$counts   = array_fill_keys( array_keys( $statuses ), 0 );

		$counts[''] = array_sum( $by_status );

		foreach ( SubscriptionStatus::get_views() as $view_key => $view ) {
			foreach ( $view['statuses'] as $status ) {
				$counts[ $view_key ] += isset( $by_status[ $status ] ) ? $by_status[ $status ] : 0;
			}
		}

		$views = array();

		foreach ( $statuses as $status_key => $label ) {
			$count = isset( $counts[ $status_key ] ) ? $counts[ $status_key ] : 0;

			$url = '' === $status_key
				? $base_url
				: add_query_arg( 'sub_status', $status_key, $base_url );

			$class = ( $current_status === $status_key ) ? ' class="current"' : '';

			$views[ '' === $status_key ? 'all' : $status_key ] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%s)</span></a>',
				esc_url( $url ),
				$class,
				esc_html( $label ),
				number_format_i18n( $count )
			);
		}

		return $views;
	}

	/**
	 * Returns the available payment forms as a map of ID => display label.
	 *
	 * @since 4.17.4
	 *
	 * @return array<int, string>
	 */
	private function get_form_options() {
		if ( null !== $this->form_options ) {
			return $this->form_options;
		}

		/** @var array<int, int> $form_ids */
		$form_ids = get_posts(
			array(
				'post_type'      => 'simple-pay',
				'post_status'    => array( 'publish', 'private', 'draft', 'pending' ),
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		$options = array();

		foreach ( $form_ids as $form_id ) {
			$form_id = absint( $form_id );

			if ( empty( $form_id ) ) {
				continue;
			}

			$form_title = trim( simpay_get_payment_form_title( $form_id ) );

			$options[ $form_id ] = '' !== $form_title
				? $form_title
				: sprintf(
					/* translators: %d Payment form ID. */
					__( 'Payment Form #%d', 'stripe' ),
					$form_id
				);
		}

		$label_counts = array_count_values( $options );

		foreach ( $options as $form_id => $label ) {
			if ( isset( $label_counts[ $label ] ) && $label_counts[ $label ] > 1 ) {
				$options[ $form_id ] = sprintf(
					/* translators: %1$s Payment form name. %2$d Payment form ID. */
					__( '%1$s (#%2$d)', 'stripe' ),
					$label,
					$form_id
				);
			}
		}

		natcasesort( $options );

		$this->form_options = $options;

		return $this->form_options;
	}

	/**
	 * Returns the display label for a payment form.
	 *
	 * @since 4.17.4
	 *
	 * @param int $form_id Payment form ID.
	 * @return string
	 */
	private function get_form_label( $form_id ) {
		$options = $this->get_form_options();

		if ( isset( $options[ $form_id ] ) ) {
			return $options[ $form_id ];
		}

		return sprintf(
			/* translators: %d Payment form ID. */
			__( 'Payment Form #%d', 'stripe' ),
			$form_id
		);
	}

	/**
	 * Renders the payment mode and payment form filters.
	 *
	 * @since 4.17.4
	 *
	 * @param string $which Top or bottom.
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		$current_mode = $this->get_current_mode();
		$current_form = $this->get_current_form_id();
		$forms        = $this->get_form_options();

		?>
		<div class="alignleft actions simpay-list-filters">
			<label for="simpay-sub-filter-livemode" class="screen-reader-text">
				<?php esc_html_e( 'Filter by payment mode', 'stripe' ); ?>
			</label>
			<select name="sub_livemode" id="simpay-sub-filter-livemode">
				<option value="live" <?php selected( $current_mode, 'live' ); ?>>
					<?php esc_html_e( 'Live Mode', 'stripe' ); ?>
				</option>
				<option value="test" <?php selected( $current_mode, 'test' ); ?>>
					<?php esc_html_e( 'Test Mode', 'stripe' ); ?>
				</option>
			</select>

			<?php if ( ! empty( $forms ) ) : ?>
				<label for="simpay-sub-filter-form-id" class="screen-reader-text">
					<?php esc_html_e( 'Filter by payment form', 'stripe' ); ?>
				</label>
				<select name="sub_form_id" id="simpay-sub-filter-form-id">
					<option value=""><?php esc_html_e( 'All Forms', 'stripe' ); ?></option>
					<?php foreach ( $forms as $form_id => $form_title ) : ?>
						<option value="<?php echo esc_attr( (string) $form_id ); ?>" <?php selected( $current_form, $form_id ); ?>>
							<?php echo esc_html( $form_title ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>

			<?php
			submit_button(
				__( 'Filter', 'stripe' ),
				'',
				'filter_action',
				false
			);
			?>
		</div>
		<?php
	}

	/**
	 * Builds the shared `WHERE` clause for subscription queries.
	 *
	 * Every fragment is produced by `$wpdb->prepare()`, so the returned string
	 * is safe to interpolate into a query.
	 *
	 * @since 4.17.4
	 *
	 * @param bool $include_status Whether to apply the status filter.
	 * @return string
	 */
	private function get_where_clause( $include_status = true ) {
		global $wpdb;

		$where = $wpdb->prepare(
			'WHERE livemode = %d',
			$this->get_current_livemode()
		);

		// Scope to the connected Stripe account, when one is connected, so
		// switching accounts never surfaces another account's data (#3533).
		$where .= AccountScope::get_where_fragment();

		$status = $this->get_current_status();

		if ( $include_status && '' !== $status ) {
			$where .= $this->get_status_where_fragment( $status );
		}

		$form_id = $this->get_current_form_id();

		if ( $form_id > 0 ) {
			$where .= $wpdb->prepare( ' AND form_id = %d', $form_id );
		}

		$search = $this->get_current_search();

		if ( '' !== $search ) {
			$search = '%' . $wpdb->esc_like( $search ) . '%';

			$where .= $wpdb->prepare(
				' AND (email LIKE %s OR customer_id LIKE %s OR _object_id LIKE %s)',
				$search,
				$search,
				$search
			);
		}

		return $where;
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page = 20;

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$where = $this->get_where_clause();

		$total_items = (int) $wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpsp_subscriptions {$where}"
		);

		$allowed_orderby = array( 'id', 'date_created', 'amount', 'current_period_end' );
		$orderby         = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], $allowed_orderby, true )
			? sanitize_text_field( $_GET['orderby'] )
			: 'id';

		$order = isset( $_GET['order'] ) && in_array( strtoupper( $_GET['order'] ), array( 'ASC', 'DESC' ), true )
			? strtoupper( sanitize_text_field( $_GET['order'] ) )
			: 'DESC';

		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// $where is composed of $wpdb->prepare() fragments and $orderby/$order
		// are validated against allowlists above, so the query is safe.
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$wpdb->prefix}wpsp_subscriptions {$where} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => (int) ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Renders the ID column.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_id( $item ) {
		$detail_url = add_query_arg(
			array(
				'post_type'       => 'simple-pay',
				'page'            => 'simpay-subscriptions',
				'subscription_id' => $item->id,
			),
			admin_url( 'edit.php' )
		);

		$actions = array(
			'view' => sprintf(
				'<a href="%s">%s</a>',
				esc_url( $detail_url ),
				esc_html__( 'View', 'stripe' )
			),
		);

		$id_link = sprintf(
			'<a href="%s"><strong>#%d</strong></a>',
			esc_url( $detail_url ),
			absint( $item->id )
		);

		$object_id = ! empty( $item->_object_id )
			? '<br><code>' . esc_html( $item->_object_id ) . '</code>'
			: '';

		return $id_link . $object_id . $this->row_actions( $actions );
	}

	/**
	 * Renders the date column.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_date_created( $item ) {
		$timestamp   = strtotime( $item->date_created );
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		return esc_html(
			date_i18n( $date_format . ' ' . $time_format, $timestamp )
		);
	}

	/**
	 * Renders the email/customer column.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_email( $item ) {
		if ( empty( $item->email ) ) {
			return '&mdash;';
		}

		$stripe_url = sprintf(
			'https://dashboard.stripe.com/%scustomers?email=%s',
			$item->livemode ? '' : 'test/',
			rawurlencode( $item->email )
		);

		return sprintf(
			'<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
			esc_url( $stripe_url ),
			esc_html( $item->email )
		);
	}

	/**
	 * Renders the form column.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_form_id( $item ) {
		if ( empty( $item->form_id ) ) {
			return '&mdash;';
		}

		$form_id    = absint( $item->form_id );
		$form_title = $this->get_form_label( $form_id );
		$edit_url   = get_edit_post_link( $form_id );

		if ( $edit_url ) {
			return sprintf(
				'<a href="%s">%s</a>',
				esc_url( $edit_url ),
				esc_html( $form_title )
			);
		}

		return esc_html( $form_title );
	}

	/**
	 * Renders the amount column, including the billing interval.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_amount( $item ) {
		$amount = esc_html(
			simpay_format_currency( $item->amount, $item->currency )
		);

		$interval = $this->get_interval_label( $item );

		if ( '' !== $interval ) {
			$amount .= sprintf(
				' <span class="simpay-txn-interval">%s</span>',
				esc_html( $interval )
			);
		}

		return $amount;
	}

	/**
	 * Returns a human-readable billing interval label, e.g. "/ month" or
	 * "/ 3 months".
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	private function get_interval_label( $item ) {
		if ( empty( $item->billing_interval ) ) {
			return '';
		}

		$count    = max( 1, (int) $item->interval_count );
		$interval = (string) $item->billing_interval;

		$labels = array(
			'day'   => _n( 'day', 'days', $count, 'stripe' ),
			'week'  => _n( 'week', 'weeks', $count, 'stripe' ),
			'month' => _n( 'month', 'months', $count, 'stripe' ),
			'year'  => _n( 'year', 'years', $count, 'stripe' ),
		);

		$word = isset( $labels[ $interval ] ) ? $labels[ $interval ] : $interval;

		if ( 1 === $count ) {
			/* translators: %s Billing interval, e.g. "month". */
			return sprintf( __( '/ %s', 'stripe' ), $word );
		}

		return sprintf(
			/* translators: %1$d Interval count. %2$s Billing interval, e.g. "months". */
			__( '/ %1$d %2$s', 'stripe' ),
			$count,
			$word
		);
	}

	/**
	 * Renders the status column.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_status( $item ) {
		return sprintf(
			'<span class="simpay-txn-status simpay-txn-status--%s">%s</span>',
			esc_attr( $item->status ),
			esc_html( $this->get_status_label( $item->status ) )
		);
	}

	/**
	 * Returns a human-readable label for a subscription status.
	 *
	 * @since 4.17.4
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	private function get_status_label( $status ) {
		return SubscriptionStatus::get_label( $status );
	}

	/**
	 * Renders the next renewal column.
	 *
	 * A subscription that will not bill again shows a dash, and one set to
	 * cancel shows its end date as "Ends ..." rather than as a renewal.
	 *
	 * @since 4.17.4
	 *
	 * @param Subscription_Row $item Subscription row.
	 * @return string
	 */
	protected function column_current_period_end( $item ) {
		$period_end_type = SubscriptionStatus::get_period_end_type(
			$item->status,
			$item->cancel_at_period_end
		);

		if ( '' === $period_end_type || empty( $item->current_period_end ) ) {
			return '&mdash;';
		}

		$timestamp = strtotime( $item->current_period_end );

		if ( false === $timestamp ) {
			return '&mdash;';
		}

		$date_format = get_option( 'date_format' );
		$date_format = is_string( $date_format ) ? $date_format : 'F j, Y';

		$date = date_i18n( $date_format, $timestamp );

		if ( SubscriptionStatus::PERIOD_END_ENDS === $period_end_type ) {
			return esc_html(
				sprintf(
					/* translators: %s Date the subscription ends. */
					__( 'Ends %s', 'stripe' ),
					$date
				)
			);
		}

		return esc_html( $date );
	}

	/**
	 * Returns the message for no items.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No subscriptions found.', 'stripe' );
	}
}
