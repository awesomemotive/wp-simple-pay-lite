<?php
/**
 * List Table: Transactions
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\Admin\ListTable;

use SimplePay\Core\Utils\AccountScope;
use WP_List_Table;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * TransactionsListTable class.
 *
 * @since 4.17.4
 *
 * @phpstan-type Transaction_Row object{
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
 * }
 */
class TransactionsListTable extends WP_List_Table {

	/**
	 * `WHERE` fragment restricting the list to one-time transactions.
	 *
	 * This page is scoped to one-time payments; subscriptions get their own
	 * table and menu page (#3534). Two columns decide what that means, because
	 * the stored `object` alone does not:
	 *
	 * - `object = 'payment_intent'` is every completed charge, whether it began
	 *   as a direct PaymentIntent, a one-time Stripe Checkout Session, or a
	 *   one-time multi-line Invoice. Checkout needs no special case: a Session
	 *   is first recorded as `object = 'checkout_session'` with zero totals and
	 *   an `open` status, then rewritten to the resulting `payment_intent` (or
	 *   `setup_intent`) by `TransactionObserver::update_on_checkout_session()`
	 *   once `checkout.session.completed` arrives -- in Lite, by
	 *   `update_on_checkout_session_lite()` when the receipt is viewed. Rows
	 *   left as `checkout_session` are therefore sessions that were never
	 *   completed, which are not payments and are excluded deliberately.
	 *
	 * - A null/empty `subscription_id` is what makes the charge one-time.
	 *   Subscription invoices -- both the initial one and every renewal -- are
	 *   also stored as `object = 'payment_intent'`, but always carry the
	 *   subscription they belong to, so they are excluded until #3534 can list
	 *   them alongside it. `object = 'setup_intent'` is excluded for the same
	 *   reason: it is only ever written for a subscription trial.
	 *
	 * The `object_status` index covers the `object` comparison and
	 * `subscription_id` is indexed, so neither condition forces a scan.
	 *
	 * @since 4.17.4
	 *
	 * @var string
	 */
	const ONE_TIME_SCOPE_SQL = "AND object = 'payment_intent' AND ( subscription_id IS NULL OR subscription_id = '' )";

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
				'singular' => 'transaction',
				'plural'   => 'transactions',
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
			'cb'           => '<input type="checkbox" />',
			'id'           => __( 'ID', 'stripe' ),
			'date_created' => __( 'Date', 'stripe' ),
			'email'        => __( 'Customer', 'stripe' ),
			'form_id'      => __( 'Form', 'stripe' ),
			'amount_total' => __( 'Amount', 'stripe' ),
			'status'       => __( 'Status', 'stripe' ),
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
			'id'           => array( 'id', true ),
			'date_created' => array( 'date_created', false ),
			'amount_total' => array( 'amount_total', false ),
		);
	}

	/**
	 * Returns available bulk actions.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	protected function get_bulk_actions() {
		return array(
			'export_csv' => __( 'Export to CSV', 'stripe' ),
		);
	}

	/**
	 * Returns the payment form the list is currently filtered by.
	 *
	 * The query variable is `txn_form_id`, not `form_id`: a bare `form_id` is
	 * claimed by the payment confirmation flow, whose `init` handler redirects
	 * any request carrying it to the form's Payment Page.
	 *
	 * Returns 0 when no (or an unusable) form is requested, in which case no
	 * form filter is applied at all -- transactions with no associated form are
	 * stored with a `form_id` of 0, so it can never be a filter value.
	 *
	 * @since 4.17.4
	 *
	 * @return int
	 */
	private function get_current_form_id() {
		return isset( $_GET['txn_form_id'] ) ? absint( $_GET['txn_form_id'] ) : 0;
	}

	/**
	 * Returns the payment mode the list is currently showing.
	 *
	 * Defaults to the global payment mode, but is an explicit choice rather
	 * than a derived one: individual payment forms can override the global
	 * setting through their `_livemode` post meta, so reading the global
	 * setting alone would silently hide the transactions of any form pinned to
	 * the other mode.
	 *
	 * The stored `livemode` column is authoritative -- it is copied from the
	 * Stripe object when the transaction is recorded -- so it is never
	 * re-derived from a form's current setting, which can change at any time.
	 *
	 * @since 4.17.4
	 *
	 * @return string Either 'live' or 'test'.
	 */
	private function get_current_mode() {
		$requested = isset( $_GET['txn_livemode'] )
			? sanitize_text_field( $_GET['txn_livemode'] )
			: '';

		if ( in_array( $requested, array( 'live', 'test' ), true ) ) {
			return $requested;
		}

		return simpay_is_test_mode() ? 'test' : 'live';
	}

	/**
	 * Returns the payment mode the list is currently showing, as stored in the
	 * `livemode` column.
	 *
	 * @since 4.17.4
	 *
	 * @return int Either 1 (live) or 0 (test).
	 */
	private function get_current_livemode() {
		return 'live' === $this->get_current_mode() ? 1 : 0;
	}

	/**
	 * Determines whether the list is currently showing live transactions.
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
		$status = isset( $_GET['txn_status'] )
			? sanitize_text_field( $_GET['txn_status'] )
			: '';

		// Only recognized view keys are valid filters; anything else falls back
		// to "All" so an arbitrary value can't smuggle a status into the query.
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
	 * The keys are display statuses, not raw Stripe statuses: `incomplete`
	 * groups the `requires_*` states, `refunded` means fully refunded, and
	 * `partial_refund` is a refund that did not cover the whole charge. Each key
	 * maps to a WHERE fragment via `get_status_where_fragment()`.
	 *
	 * @since 4.17.4
	 *
	 * @return array<string, string>
	 */
	private function get_status_views() {
		return array(
			''               => __( 'All', 'stripe' ),
			'succeeded'      => __( 'Succeeded', 'stripe' ),
			'processing'     => __( 'Processing', 'stripe' ),
			'incomplete'     => __( 'Incomplete', 'stripe' ),
			'failed'         => __( 'Failed', 'stripe' ),
			'refunded'       => __( 'Refunded', 'stripe' ),
			'partial_refund' => __( 'Partially Refunded', 'stripe' ),
			'disputed'       => __( 'Disputed', 'stripe' ),
			'canceled'       => __( 'Canceled', 'stripe' ),
		);
	}

	/**
	 * Returns the `AND ...` WHERE fragment for a status view key.
	 *
	 * The grouped/derived keys (`incomplete`, `refunded`, `partial_refund`) use
	 * constant SQL with no user input; the remaining keys map to an exact,
	 * prepared `status = %s` match.
	 *
	 * @since 4.17.4
	 *
	 * @param string $status_key Status view key.
	 * @return string
	 */
	private function get_status_where_fragment( $status_key ) {
		global $wpdb;

		switch ( $status_key ) {
			case 'incomplete':
				return " AND status IN ( 'requires_payment_method', 'requires_confirmation', 'requires_action' )";
			case 'refunded':
				return " AND status = 'refunded' AND amount_refunded >= amount_total";
			case 'partial_refund':
				return " AND status = 'refunded' AND amount_refunded > 0 AND amount_refunded < amount_total";
			default:
				return $wpdb->prepare( ' AND status = %s', $status_key );
		}
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

		// Preserve the active mode, form and search filters so switching status
		// views does not silently reset them.
		$base_args = array(
			'post_type'    => 'simple-pay',
			'page'         => 'simpay-transactions',
			'txn_livemode' => $this->get_current_mode(),
		);

		if ( $current_form > 0 ) {
			$base_args['txn_form_id'] = $current_form;
		}

		if ( '' !== $current_search ) {
			$base_args['s'] = $current_search;
		}

		$base_url = add_query_arg( $base_args, admin_url( 'edit.php' ) );

		// One pass computes every view's count so the tabs and the "All" total
		// always agree with what each view lists. Counts reflect every active
		// filter except the status itself.
		$where = $this->get_where_clause( false );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $where is built from $wpdb->prepare() fragments; the CASE expressions are constant SQL with no user input.
		$row = $wpdb->get_row(
			"SELECT
				COUNT(*) AS total,
				SUM( status = 'succeeded' ) AS succeeded,
				SUM( status = 'processing' ) AS processing,
				SUM( status IN ( 'requires_payment_method', 'requires_confirmation', 'requires_action' ) ) AS incomplete,
				SUM( status = 'failed' ) AS failed,
				SUM( status = 'refunded' AND amount_refunded >= amount_total ) AS refunded,
				SUM( status = 'refunded' AND amount_refunded > 0 AND amount_refunded < amount_total ) AS partial_refund,
				SUM( status = 'disputed' ) AS disputed,
				SUM( status = 'canceled' ) AS canceled
			FROM {$wpdb->prefix}wpsp_transactions {$where}"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		$statuses = $this->get_status_views();
		$counts   = array_fill_keys( array_keys( $statuses ), 0 );

		if ( $row ) {
			$counts['']               = (int) $row->total;
			$counts['succeeded']      = (int) $row->succeeded;
			$counts['processing']     = (int) $row->processing;
			$counts['incomplete']     = (int) $row->incomplete;
			$counts['failed']         = (int) $row->failed;
			$counts['refunded']       = (int) $row->refunded;
			$counts['partial_refund'] = (int) $row->partial_refund;
			$counts['disputed']       = (int) $row->disputed;
			$counts['canceled']       = (int) $row->canceled;
		}

		$views = array();

		foreach ( $statuses as $status_key => $label ) {
			$count = isset( $counts[ $status_key ] ) ? $counts[ $status_key ] : 0;

			$url = '' === $status_key
				? $base_url
				: add_query_arg( 'txn_status', $status_key, $base_url );

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
	 * Labels come from `simpay_get_payment_form_title()` -- the same resolution
	 * the Payment Forms list table uses -- because a form's name lives in the
	 * `_company_name` post meta and its `post_title` is usually empty. Reading
	 * it through `get_the_title()` instead would depend on a `the_title`
	 * callback registered in an admin-only legacy file.
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

		// Forms are not required to have unique names -- new forms all default
		// to "Payment Form" -- so append the ID to any repeated label to keep
		// the options distinguishable.
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

		// Sort by the resolved label, not by the (often empty) post title.
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

		// The form has been deleted, but transactions still reference it.
		return sprintf(
			/* translators: %d Payment form ID. */
			__( 'Payment Form #%d', 'stripe' ),
			$form_id
		);
	}

	/**
	 * Renders the payment mode and payment form filters in the extra table
	 * navigation.
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
			<label for="simpay-txn-filter-livemode" class="screen-reader-text">
				<?php esc_html_e( 'Filter by payment mode', 'stripe' ); ?>
			</label>
			<select name="txn_livemode" id="simpay-txn-filter-livemode">
				<option value="live" <?php selected( $current_mode, 'live' ); ?>>
					<?php esc_html_e( 'Live Mode', 'stripe' ); ?>
				</option>
				<option value="test" <?php selected( $current_mode, 'test' ); ?>>
					<?php esc_html_e( 'Test Mode', 'stripe' ); ?>
				</option>
			</select>

			<?php if ( ! empty( $forms ) ) : ?>
				<label for="simpay-txn-filter-form-id" class="screen-reader-text">
					<?php esc_html_e( 'Filter by payment form', 'stripe' ); ?>
				</label>
				<select name="txn_form_id" id="simpay-txn-filter-form-id">
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
	 * Builds the `WHERE` clause restricting a query to the rows this page is
	 * allowed to read.
	 *
	 * Three conditions define that: the payment mode being viewed, the
	 * one-time scope, and the connected Stripe account. They are the isolation
	 * boundary rather than a display preference, so they belong to every query
	 * the table runs -- including the CSV export, which picks rows by ID and
	 * would otherwise be a way to read straight past them (#3533).
	 *
	 * The account predicate comes from AccountScope, shared with the
	 * subscriptions list and both detail views so there is one definition of
	 * it.
	 *
	 * Every fragment is produced by `$wpdb->prepare()` and contains no `LIKE`
	 * wildcards, so the returned string is safe to interpolate into a query.
	 *
	 * @since 4.17.4
	 *
	 * @return string
	 */
	private function get_scope_clause() {
		global $wpdb;

		$where = $wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- A constant SQL fragment with no placeholders.
			'WHERE livemode = %d ' . self::ONE_TIME_SCOPE_SQL,
			$this->get_current_livemode()
		);

		$where .= AccountScope::get_where_fragment();

		return $where;
	}

	/**
	 * Builds the shared `WHERE` clause for transaction queries.
	 *
	 * Every fragment is produced by `$wpdb->prepare()`, so the returned string
	 * is safe to interpolate into a query.
	 *
	 * @since 4.17.4
	 *
	 * @param bool $include_status Whether to apply the status filter. Pass
	 *                             false when counting rows per status.
	 * @return string
	 */
	private function get_where_clause( $include_status = true ) {
		global $wpdb;

		$where = $this->get_scope_clause();

		// Status filter. View keys can be grouped/derived (e.g. `incomplete`,
		// `partial_refund`), so resolve each to its own WHERE fragment.
		$status = $this->get_current_status();

		if ( $include_status && '' !== $status ) {
			$where .= $this->get_status_where_fragment( $status );
		}

		// Payment form filter.
		$form_id = $this->get_current_form_id();

		if ( $form_id > 0 ) {
			$where .= $wpdb->prepare( ' AND form_id = %d', $form_id );
		}

		// Search.
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

		// Total items. The {$where} clause is built exclusively from
		// $wpdb->prepare() fragments, so it is already escaped.
		$total_items = (int) $wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpsp_transactions {$where}"
		);

		// Sorting.
		$allowed_orderby = array( 'id', 'date_created', 'amount_total' );
		$orderby         = isset( $_GET['orderby'] ) && in_array( $_GET['orderby'], $allowed_orderby, true )
			? sanitize_text_field( $_GET['orderby'] )
			: 'id';

		$order = isset( $_GET['order'] ) && in_array( strtoupper( $_GET['order'] ), array( 'ASC', 'DESC' ), true )
			? strtoupper( sanitize_text_field( $_GET['order'] ) )
			: 'DESC';

		// Pagination.
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// $where is composed of $wpdb->prepare() fragments and $orderby/$order
		// are validated against allowlists above. LIMIT/OFFSET are cast to
		// integers here rather than passed through a second prepare(): $where
		// can carry LIKE wildcards from the search term, and an already
		// prepared fragment must not be fed back in as a format string.
		$limit = sprintf( 'LIMIT %d OFFSET %d', absint( $per_page ), absint( $offset ) );

		$this->items = $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared
			"SELECT * FROM {$wpdb->prefix}wpsp_transactions {$where} ORDER BY {$orderby} {$order} {$limit}"
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
	 * Renders the checkbox column.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="transaction_ids[]" value="%d" />',
			absint( $item->id )
		);
	}

	/**
	 * Renders the ID column.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	protected function column_id( $item ) {
		$detail_url = add_query_arg(
			array(
				'post_type'      => 'simple-pay',
				'page'           => 'simpay-transactions',
				'transaction_id' => $item->id,
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
	 * @param Transaction_Row $item Transaction row.
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
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	protected function column_email( $item ) {
		if ( empty( $item->email ) ) {
			return '&mdash;';
		}

		// The row's own mode, not the global setting: a form pinned to the
		// opposite mode would otherwise link to the wrong Stripe dashboard.
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
	 * @param Transaction_Row $item Transaction row.
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
	 * Renders the amount column.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	protected function column_amount_total( $item ) {
		$output = esc_html(
			simpay_format_currency( $item->amount_total, $item->currency )
		);

		if ( ! empty( $item->amount_refunded ) && $item->amount_refunded > 0 ) {
			$output .= sprintf(
				' <span class="simpay-txn-refunded-amount">-%s</span>',
				esc_html(
					simpay_format_currency(
						$item->amount_refunded,
						$item->currency
					)
				)
			);
		}

		return $output;
	}

	/**
	 * Renders the status column.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	protected function column_status( $item ) {
		$status = $this->get_display_status( $item );

		return sprintf(
			'<span class="simpay-txn-status simpay-txn-status--%s">%s</span>',
			esc_attr( $status ),
			esc_html( $this->get_status_label( $status ) )
		);
	}

	/**
	 * Returns the display status, accounting for partial refunds.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $item Transaction row.
	 * @return string
	 */
	private function get_display_status( $item ) {
		if ( 'refunded' === $item->status ) {
			$refunded = absint( $item->amount_refunded );
			$total    = absint( $item->amount_total );

			// A refund that does not cover the full charge is a partial refund.
			// Mirrors the integer comparison used in the detail view rather than
			// comparing the raw string columns.
			if ( $refunded > 0 && $refunded < $total ) {
				return 'partial_refund';
			}
		}

		return $item->status;
	}

	/**
	 * Returns a human-readable label for a status.
	 *
	 * @since 4.17.4
	 *
	 * @param string $status Status key.
	 * @return string
	 */
	private function get_status_label( $status ) {
		$labels = array(
			'succeeded'               => __( 'Succeeded', 'stripe' ),
			'failed'                  => __( 'Failed', 'stripe' ),
			'refunded'                => __( 'Refunded', 'stripe' ),
			'partial_refund'          => __( 'Partially Refunded', 'stripe' ),
			'disputed'                => __( 'Disputed', 'stripe' ),
			'canceled'                => __( 'Canceled', 'stripe' ),
			'requires_payment_method' => __( 'Incomplete', 'stripe' ),
			'requires_confirmation'   => __( 'Incomplete', 'stripe' ),
			'requires_action'         => __( 'Incomplete', 'stripe' ),
			'processing'              => __( 'Processing', 'stripe' ),
		);

		return isset( $labels[ $status ] ) ? $labels[ $status ] : ucfirst( $status );
	}

	/**
	 * Returns the message for no items.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No transactions found.', 'stripe' );

		$hint = $this->get_mode_mismatch_hint();

		if ( '' !== $hint ) {
			echo ' ' . wp_kses(
				$hint,
				array(
					'a' => array( 'href' => array() ),
				)
			);
		}
	}

	/**
	 * Returns a hint when the filtered payment form runs in the mode that is
	 * not currently being viewed.
	 *
	 * A form pinned to the opposite mode through its `_livemode` post meta has
	 * none of its transactions listed, which otherwise looks like a form that
	 * has never been paid.
	 *
	 * @since 4.17.4
	 *
	 * @return string Hint markup, or an empty string when the modes agree.
	 */
	private function get_mode_mismatch_hint() {
		$form_id = $this->get_current_form_id();

		if ( 0 === $form_id ) {
			return '';
		}

		$form = simpay_get_form( $form_id );

		if ( false === $form ) {
			return '';
		}

		$form_mode = $form->is_livemode() ? 'live' : 'test';

		if ( $form_mode === $this->get_current_mode() ) {
			return '';
		}

		$switch_url = add_query_arg(
			'txn_livemode',
			$form_mode,
			$this->get_current_url()
		);

		return sprintf(
			'live' === $form_mode
				/* translators: %1$s Payment form name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				? __( '%1$s runs in Live Mode. %2$sView its live transactions%3$s.', 'stripe' )
				/* translators: %1$s Payment form name. %2$s Opening anchor tag, do not translate. %3$s Closing anchor tag, do not translate. */
				: __( '%1$s runs in Test Mode. %2$sView its test transactions%3$s.', 'stripe' ),
			esc_html( $this->get_form_label( $form_id ) ),
			'<a href="' . esc_url( $switch_url ) . '">',
			'</a>'
		);
	}

	/**
	 * Returns the current screen URL with the payment mode removed.
	 *
	 * @since 4.17.4
	 *
	 * @return string
	 */
	private function get_current_url() {
		$args = array(
			'post_type' => 'simple-pay',
			'page'      => 'simpay-transactions',
		);

		$form_id = $this->get_current_form_id();

		if ( $form_id > 0 ) {
			$args['txn_form_id'] = $form_id;
		}

		$status = $this->get_current_status();

		if ( '' !== $status ) {
			$args['txn_status'] = $status;
		}

		$search = $this->get_current_search();

		if ( '' !== $search ) {
			$args['s'] = $search;
		}

		return add_query_arg( $args, admin_url( 'edit.php' ) );
	}

	/**
	 * Generates CSV data for selected or all visible transactions.
	 *
	 * The IDs arrive from the request, so the same one-time scope the list is
	 * built on is reapplied here -- the export cannot hold a row the page would
	 * not list.
	 *
	 * @since 4.17.4
	 *
	 * @param array<int> $ids Transaction IDs to export.
	 * @return array<int, Transaction_Row>
	 */
	public function get_export_data( $ids ) {
		global $wpdb;

		if ( empty( $ids ) ) {
			return array();
		}

		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// The requested IDs are narrowed by the same scope clause the list
		// itself uses. Without it, an ID was the only thing the export
		// checked, so passing one by hand exported a row from the other
		// payment mode or from a previously connected Stripe account -- the
		// isolation the list enforces, bypassed (#3533). The IDs come from
		// checkboxes on an already-scoped page, so a well-formed request loses
		// nothing; a request naming rows outside the scope now exports none of
		// them.
		$where = $this->get_scope_clause();

		// $placeholders is a list of %d tokens generated from $ids above, so
		// the interpolated IN() clause is fully parameterized by $wpdb->prepare().
		// $where is composed of $wpdb->prepare() fragments, so it is already
		// escaped, and it carries no LIKE wildcards to be re-interpreted here.
		/** @var array<int, Transaction_Row> $results */
		$results = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				"SELECT * FROM {$wpdb->prefix}wpsp_transactions {$where} AND id IN ({$placeholders})",
				$ids
			)
		);

		return $results ? $results : array();
	}
}
