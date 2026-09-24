<?php
/**
 * Admin: "Transactions" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.17.4
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\Admin\ListTable\TransactionsListTable;
use SimplePay\Core\Utils\AccountScope;

/**
 * TransactionsPage class.
 *
 * @since 4.17.4
 *
 * @phpstan-import-type Transaction_Row from TransactionsListTable
 */
class TransactionsPage extends AbstractAdminPage implements AdminSecondaryPageInterface {

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		return __( 'Transactions', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'Transactions', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay-transactions';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		wp_enqueue_style(
			'simpay-admin-page-transactions',
			SIMPLE_PAY_INC_URL . 'core/assets/css/simpay-admin-page-transactions.css',
			array(),
			SIMPLE_PAY_VERSION
		);

		// Detail view.
		if ( ! empty( $_GET['transaction_id'] ) ) {
			$this->render_detail_view();
			return;
		}

		// List view.
		$this->render_list_view();
	}

	/**
	 * Renders the list view.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function render_list_view() {
		$list_table = new TransactionsListTable();
		$list_table->prepare_items();

		// @todo use a ViewLoader
		// `include`, not `include_once`: a template renders wherever it is
		// asked to, and rendering one twice in a process must not silently
		// produce nothing the second time.
		include SIMPLE_PAY_DIR . '/views/admin-page-transactions.php';
	}

	/**
	 * Renders the detail view for a single transaction.
	 *
	 * Scoped to the connected Stripe account, like the list, the status counts
	 * and the CSV export (#3533). Without it a transaction belonging to a
	 * previously connected account stayed reachable by ID, through the one
	 * query where the ID was the whole of the lookup.
	 *
	 * The payment mode is deliberately not part of the scope. A payment form
	 * can override the global mode, so both modes' rows coexist and this view
	 * is meant to open either one -- the back link derives the list's mode
	 * from the row itself rather than from the request.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	private function render_detail_view() {
		global $wpdb;

		$transaction_id = absint( $_GET['transaction_id'] );
		$account_scope  = AccountScope::get_where_fragment();

		/** @var Transaction_Row|null $transaction */
		$transaction = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $account_scope is itself a prepared fragment; only the table name is interpolated.
				"SELECT * FROM {$wpdb->prefix}wpsp_transactions WHERE id = %d{$account_scope}",
				$transaction_id
			)
		);

		if ( ! $transaction ) {
			wp_die( esc_html__( 'Transaction not found.', 'stripe' ) );
		}

		// Determine if this transaction is refundable.
		$is_refundable = (
			'payment_intent' === $transaction->object &&
			in_array( $transaction->status, array( 'succeeded', 'refunded' ), true ) &&
			absint( $transaction->amount_refunded ) < absint( $transaction->amount_total )
		);

		$can_refund = $is_refundable && simpay_get_license()->is_pro( 'professional', '>=' );

		// Retrieve the PaymentIntent once (when applicable) so the detail view
		// can surface metadata, custom fields, the payment method and receipt
		// without making multiple Stripe API calls.
		$payment_intent = self::get_transaction_payment_intent( $transaction );
		$metadata       = self::get_payment_intent_metadata( $payment_intent );

		$form_id          = absint( $transaction->form_id );
		$file_field_map   = self::get_file_upload_field_map( $form_id );
		$file_upload_keys = array_keys( $file_field_map );
		$custom_field_map = self::get_custom_field_label_map( $form_id );

		// Files uploaded through the form's File Upload fields.
		$file_uploads = self::match_file_uploads( $file_field_map, $metadata );

		// Submitted custom field values, and any remaining raw metadata.
		$custom_fields = self::get_custom_field_rows( $metadata, $custom_field_map, $file_upload_keys );
		$metadata_rows = self::get_other_metadata_rows( $metadata, $custom_field_map, $file_upload_keys );

		// Payment method and receipt details from the latest charge.
		$payment_method = self::get_payment_method_label( $payment_intent );
		$receipt_url    = self::get_receipt_url( $payment_intent );

		// Customer name (stored on the Stripe Customer, not in metadata).
		$customer_name = self::get_customer_name( $payment_intent );

		// Rows without a PaymentIntent (e.g. SetupIntents) still carry a
		// Customer ID, so read the name from the Customer directly.
		if ( '' === $customer_name ) {
			$customer_name = self::get_stripe_customer_name( $transaction );
		}

		// Enqueue the detail-view script (powers copy-to-clipboard, and the
		// refund modal when refunds are available).
		wp_enqueue_script(
			'simpay-admin-transactions',
			SIMPLE_PAY_INC_URL . 'core/assets/js/simpay-admin-transactions.js',
			array(),
			SIMPLE_PAY_VERSION,
			true
		);

		// Localize refund modal config if the user can refund.
		if ( $can_refund ) {
			$is_zero_decimal = simpay_is_zero_decimal( $transaction->currency );
			$max_refundable  = absint( $transaction->amount_total ) - absint( $transaction->amount_refunded );
			$display_max     = $is_zero_decimal ? $max_refundable : round( $max_refundable / 100, 2 );

			wp_localize_script(
				'simpay-admin-transactions',
				'simpayAdminTransactions',
				array(
					'nonce'         => wp_create_nonce( 'simpay-refund-transaction' ),
					'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
					'transactionId' => absint( $transaction->id ),
					'maxRefundable' => $display_max,
					'isZeroDecimal' => $is_zero_decimal,
					'currency'      => strtoupper( $transaction->currency ),
					'i18n'          => array(
						'processing'    => __( 'Processing...', 'stripe' ),
						'success'       => __( 'Refund processed successfully. Reloading...', 'stripe' ),
						'error'         => __( 'An error occurred while processing the refund.', 'stripe' ),
						'invalidAmount' => __( 'Please enter a valid refund amount.', 'stripe' ),
						'confirm'       => __( 'Are you sure you want to process this refund? This cannot be undone.', 'stripe' ),
					),
				)
			);
		}

		// @todo use a ViewLoader
		include SIMPLE_PAY_DIR . '/views/admin-page-transaction-detail.php';
	}

	/**
	 * Returns the Stripe secret key matching a transaction's mode.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $transaction Transaction row.
	 * @return string Secret key, or an empty string when none is configured.
	 */
	private static function get_transaction_api_key( $transaction ) {
		$api_key = $transaction->livemode
			? simpay_get_setting( 'live_secret_key', '' )
			: simpay_get_setting( 'test_secret_key', '' );

		return is_string( $api_key ) ? $api_key : '';
	}

	/**
	 * Returns the name stored on a transaction's Stripe Customer.
	 *
	 * Used when the PaymentIntent cannot supply the name, such as for
	 * SetupIntent rows. Returns an empty string when the row has no Customer,
	 * no API key is configured, or on any Stripe API error.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $transaction Transaction row.
	 * @return string
	 */
	private static function get_stripe_customer_name( $transaction ) {
		if ( empty( $transaction->customer_id ) ) {
			return '';
		}

		$api_key = self::get_transaction_api_key( $transaction );

		if ( '' === $api_key ) {
			return '';
		}

		try {
			$customer = \SimplePay\Core\API\Customers\retrieve(
				$transaction->customer_id,
				array(
					'api_key' => $api_key,
				)
			);
		} catch ( \Exception $e ) {
			return '';
		}

		if ( ! is_object( $customer ) || empty( $customer->name ) ) {
			return '';
		}

		return (string) $customer->name;
	}

	/**
	 * Retrieves the Stripe PaymentIntent for a transaction.
	 *
	 * The latest charge is expanded so the detail view can surface the payment
	 * method and receipt URL. Returns null for non-PaymentIntent transactions,
	 * when no API key is configured, or on any Stripe API error.
	 *
	 * @since 4.17.4
	 *
	 * @param Transaction_Row $transaction Transaction row.
	 * @return \SimplePay\Vendor\Stripe\PaymentIntent|null
	 */
	private static function get_transaction_payment_intent( $transaction ) {
		if (
			'payment_intent' !== $transaction->object ||
			empty( $transaction->_object_id )
		) {
			return null;
		}

		$api_key = self::get_transaction_api_key( $transaction );

		if ( '' === $api_key ) {
			return null;
		}

		try {
			return \SimplePay\Core\API\PaymentIntents\retrieve(
				array(
					'id'     => $transaction->_object_id,
					'expand' => array( 'latest_charge', 'customer' ),
				),
				array(
					'api_key' => $api_key,
				)
			);
		} catch ( \Exception $e ) {
			return null;
		}
	}

	/**
	 * Returns the metadata array for a PaymentIntent.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent|null $payment_intent PaymentIntent.
	 * @return array<string, string>
	 */
	private static function get_payment_intent_metadata( $payment_intent ) {
		if ( null === $payment_intent || ! isset( $payment_intent->metadata ) ) {
			return array();
		}

		/** @var array<string, string> $metadata */
		$metadata = $payment_intent->metadata->toArray();

		return $metadata;
	}

	/**
	 * Returns a human-readable payment method label from the latest charge.
	 *
	 * For card payments this is the brand and last four digits, e.g.
	 * "Visa ending in 4242". Returns an empty string when unavailable.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent|null $payment_intent PaymentIntent.
	 * @return string
	 */
	private static function get_payment_method_label( $payment_intent ) {
		if ( null === $payment_intent || ! isset( $payment_intent->latest_charge ) ) {
			return '';
		}

		$charge = $payment_intent->latest_charge;

		if ( ! is_object( $charge ) || ! isset( $charge->payment_method_details ) ) {
			return '';
		}

		$details = $charge->payment_method_details;

		if ( ! isset( $details->card ) || ! isset( $details->card->brand ) ) {
			return '';
		}

		$brand = ucfirst( (string) $details->card->brand );
		$last4 = isset( $details->card->last4 ) ? (string) $details->card->last4 : '';

		if ( '' === $last4 ) {
			return $brand;
		}

		return sprintf(
			/* translators: %1$s: Card brand (e.g. Visa). %2$s: Last four digits of the card. */
			__( '%1$s ending in %2$s', 'stripe' ),
			$brand,
			$last4
		);
	}

	/**
	 * Returns the Stripe receipt URL from the latest charge, if available.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent|null $payment_intent PaymentIntent.
	 * @return string
	 */
	private static function get_receipt_url( $payment_intent ) {
		if ( null === $payment_intent || ! isset( $payment_intent->latest_charge ) ) {
			return '';
		}

		$charge = $payment_intent->latest_charge;

		if ( is_object( $charge ) && isset( $charge->receipt_url ) && ! empty( $charge->receipt_url ) ) {
			return (string) $charge->receipt_url;
		}

		return '';
	}

	/**
	 * Returns the customer name for a transaction.
	 *
	 * The dedicated "Name" field is stored on the Stripe Customer (not in
	 * PaymentIntent metadata), so it is read from the expanded Customer with a
	 * fallback to the latest charge's billing details.
	 *
	 * @since 4.17.4
	 *
	 * @param \SimplePay\Vendor\Stripe\PaymentIntent|null $payment_intent PaymentIntent.
	 * @return string
	 */
	private static function get_customer_name( $payment_intent ) {
		if ( null === $payment_intent ) {
			return '';
		}

		// Prefer the Stripe Customer name.
		if (
			isset( $payment_intent->customer ) &&
			is_object( $payment_intent->customer ) &&
			isset( $payment_intent->customer->name ) &&
			! empty( $payment_intent->customer->name )
		) {
			return (string) $payment_intent->customer->name;
		}

		// Fall back to the latest charge's billing details.
		if ( isset( $payment_intent->latest_charge ) && is_object( $payment_intent->latest_charge ) ) {
			$charge = $payment_intent->latest_charge;

			if (
				isset( $charge->billing_details ) &&
				isset( $charge->billing_details->name ) &&
				! empty( $charge->billing_details->name )
			) {
				return (string) $charge->billing_details->name;
			}
		}

		return '';
	}

	/**
	 * Builds the Stripe metadata key for a custom field configuration.
	 *
	 * The key mirrors the field's frontend hidden input name: the custom
	 * "metadata" setting when present, otherwise the generated field id.
	 *
	 * @since 4.17.4
	 *
	 * @param int                  $form_id Payment form ID.
	 * @param array<string, mixed> $field   Field configuration.
	 * @return string
	 */
	private static function get_field_metadata_key( $form_id, $field ) {
		$uid = isset( $field['uid'] ) && is_scalar( $field['uid'] )
			? (string) $field['uid']
			: '';

		$metadata = isset( $field['metadata'] ) && is_scalar( $field['metadata'] )
			? trim( (string) $field['metadata'] )
			: '';

		return '' !== $metadata
			? $metadata
			: 'simpay-form-' . $form_id . '-field-' . $uid;
	}

	/**
	 * Builds a map of Stripe metadata key => label for a form's File Upload fields.
	 *
	 * @since 4.17.4
	 *
	 * @param int $form_id Payment form ID.
	 * @return array<string, string>
	 */
	public static function get_file_upload_field_map( $form_id ) {
		$custom_fields = get_post_meta( $form_id, '_custom_fields', true );

		if ( ! is_array( $custom_fields ) || ! isset( $custom_fields['file_upload'] ) ) {
			return array();
		}

		$map = array();

		foreach ( $custom_fields['file_upload'] as $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}

			$key = self::get_field_metadata_key( $form_id, $field );

			$label = isset( $field['label'] ) && '' !== trim( (string) $field['label'] )
				? (string) $field['label']
				: esc_html__( 'File Upload', 'stripe' );

			$map[ $key ] = $label;
		}

		return $map;
	}

	/**
	 * Builds a map of Stripe metadata key => label for ALL of a form's custom fields.
	 *
	 * Used to display submitted custom field values on the detail view with
	 * their human-readable labels rather than raw metadata keys.
	 *
	 * @since 4.17.4
	 *
	 * @param int $form_id Payment form ID.
	 * @return array<string, string>
	 */
	public static function get_custom_field_label_map( $form_id ) {
		$custom_fields = get_post_meta( $form_id, '_custom_fields', true );

		if ( ! is_array( $custom_fields ) ) {
			return array();
		}

		$map = array();

		foreach ( $custom_fields as $fields ) {
			if ( ! is_array( $fields ) ) {
				continue;
			}

			foreach ( $fields as $field ) {
				if ( ! is_array( $field ) ) {
					continue;
				}

				$key = self::get_field_metadata_key( $form_id, $field );

				$label = isset( $field['label'] ) && '' !== trim( (string) $field['label'] )
					? (string) $field['label']
					: $key;

				$map[ $key ] = $label;
			}
		}

		return $map;
	}

	/**
	 * Returns submitted custom field rows for display.
	 *
	 * Limited to metadata keys that map to a known custom field, excluding
	 * File Upload fields (which are displayed separately).
	 *
	 * @since 4.17.4
	 *
	 * @param array<string, string> $metadata         PaymentIntent metadata.
	 * @param array<string, string> $label_map        Map of metadata key => label.
	 * @param array<int, string>    $file_upload_keys Keys handled by the file upload card.
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function get_custom_field_rows( $metadata, $label_map, $file_upload_keys ) {
		$rows = array();

		foreach ( $label_map as $key => $label ) {
			if ( in_array( $key, $file_upload_keys, true ) ) {
				continue;
			}

			if ( ! isset( $metadata[ $key ] ) ) {
				continue;
			}

			$value = trim( (string) $metadata[ $key ] );

			if ( '' === $value ) {
				continue;
			}

			$rows[] = array(
				'label' => (string) $label,
				'value' => $value,
			);
		}

		return $rows;
	}

	/**
	 * Returns remaining (raw) metadata rows for display.
	 *
	 * Anything not surfaced as a custom field or a file upload — typically the
	 * plugin's internal `simpay_*` metadata.
	 *
	 * @since 4.17.4
	 *
	 * @param array<string, string> $metadata         PaymentIntent metadata.
	 * @param array<string, string> $label_map        Map of custom field metadata key => label.
	 * @param array<int, string>    $file_upload_keys Keys handled by the file upload card.
	 * @return array<int, array{key: string, value: string}>
	 */
	public static function get_other_metadata_rows( $metadata, $label_map, $file_upload_keys ) {
		$rows = array();

		foreach ( $metadata as $key => $value ) {
			if ( isset( $label_map[ $key ] ) || in_array( $key, $file_upload_keys, true ) ) {
				continue;
			}

			$value = trim( (string) $value );

			if ( '' === $value ) {
				continue;
			}

			$rows[] = array(
				'key'   => (string) $key,
				'value' => $value,
			);
		}

		return $rows;
	}

	/**
	 * Matches File Upload field keys against PaymentIntent metadata.
	 *
	 * @since 4.17.4
	 *
	 * @param array<string, string> $field_map Map of metadata key => field label.
	 * @param array<string, string> $metadata  PaymentIntent metadata.
	 * @return array<int, array{label: string, url: string, filename: string}>
	 */
	public static function match_file_uploads( $field_map, $metadata ) {
		$uploads = array();

		foreach ( $field_map as $key => $label ) {
			if ( ! isset( $metadata[ $key ] ) ) {
				continue;
			}

			$url = trim( (string) $metadata[ $key ] );

			if ( '' === $url ) {
				continue;
			}

			$path     = (string) wp_parse_url( $url, PHP_URL_PATH );
			$filename = '' !== $path ? basename( $path ) : $url;

			$uploads[] = array(
				'label'    => $label,
				'url'      => $url,
				'filename' => $filename,
			);
		}

		return $uploads;
	}

	/**
	 * Handles CSV export on admin_init.
	 *
	 * This is called via the `admin_init` hook — it must be hooked separately
	 * from the render method because headers need to be sent before any output.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public static function maybe_handle_csv_export() {
		if (
			! isset( $_GET['page'] ) ||
			'simpay-transactions' !== $_GET['page']
		) {
			return;
		}

		if (
			! isset( $_GET['action'] ) ||
			'export_csv' !== $_GET['action']
		) {
			// Also check action2 for bottom bulk action.
			if (
				! isset( $_GET['action2'] ) ||
				'export_csv' !== $_GET['action2']
			) {
				return;
			}
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			! isset( $_GET['_wpnonce'] ) ||
			! wp_verify_nonce( $_GET['_wpnonce'], 'bulk-transactions' )
		) {
			return;
		}

		$ids = isset( $_GET['transaction_ids'] )
			? array_map( 'absint', (array) $_GET['transaction_ids'] )
			: array();

		if ( empty( $ids ) ) {
			return;
		}

		$list_table = new TransactionsListTable();
		$rows       = $list_table->get_export_data( $ids );

		if ( empty( $rows ) ) {
			return;
		}

		$filename = 'simpay-transactions-' . gmdate( 'Y-m-d' ) . '.csv';

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=' . $filename );

		$output = fopen( 'php://output', 'w' );

		if ( false === $output ) {
			return;
		}

		// Header row.
		fputcsv(
			$output,
			array(
				'ID',
				'Date Created',
				'Date Modified',
				'Object',
				'Object ID',
				'Email',
				'Customer ID',
				'Form ID',
				'Amount Total',
				'Amount Subtotal',
				'Amount Tax',
				'Amount Shipping',
				'Amount Discount',
				'Amount Refunded',
				'Currency',
				'Payment Method Type',
				'Status',
				'Subscription ID',
				'IP Address',
				'Livemode',
				'UUID',
			)
		);

		foreach ( $rows as $row ) {
			fputcsv(
				$output,
				array_map(
					array( self::class, 'escape_csv_value' ),
					array(
						$row->id,
						$row->date_created,
						$row->date_modified,
						$row->object,
						$row->_object_id,
						$row->email,
						$row->customer_id,
						$row->form_id,
						$row->amount_total,
						$row->amount_subtotal,
						$row->amount_tax,
						$row->amount_shipping,
						$row->amount_discount,
						$row->amount_refunded,
						$row->currency,
						$row->payment_method_type,
						$row->status,
						$row->subscription_id,
						$row->ip_address,
						$row->livemode,
						$row->uuid,
					)
				)
			);
		}

		fclose( $output );
		exit;
	}

	/**
	 * Escapes a cell so a spreadsheet cannot execute it as a formula.
	 *
	 * Excel, LibreOffice and Google Sheets evaluate a cell whose first
	 * character is `=`, `+`, `-`, `@`, a tab or a carriage return. Several
	 * exported columns carry values the customer supplied -- the email address
	 * above all -- so a payment made with a name or address of
	 * `=cmd|' /C calc'!A0` reaches the file as a live formula, and the person
	 * who opens it is an administrator on their own machine. Prefixing with a
	 * single quote makes the spreadsheet read the cell as text; the quote is
	 * not part of the value and does not appear in the cell.
	 *
	 * Numeric values are returned untouched, so the amount columns stay
	 * numbers: a legitimate `-500` must not become text just because it starts
	 * with a minus. A payload that merely opens with one, like `-2+3+cmd`, is
	 * not numeric and is still escaped.
	 *
	 * @since 4.17.4
	 *
	 * @param bool|float|int|string|null $value Cell value.
	 * @return bool|float|int|string|null
	 */
	private static function escape_csv_value( $value ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return $value;
		}

		// Signed amounts and IDs are data, not formulas.
		if ( is_numeric( $value ) ) {
			return $value;
		}

		if ( 1 !== preg_match( '/^[=+\-@\t\r]/', $value ) ) {
			return $value;
		}

		return "'" . $value;
	}

	/**
	 * Handles AJAX refund processing.
	 *
	 * @since 4.17.4
	 *
	 * @return void
	 */
	public static function handle_refund_ajax() {
		// Verify nonce.
		if (
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( $_POST['nonce'] ), 'simpay-refund-transaction' )
		) {
			wp_send_json_error(
				array( 'message' => __( 'Security check failed.', 'stripe' ) )
			);
		}

		// Check capability.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to do this.', 'stripe' ) )
			);
		}

		// Check license.
		if ( ! simpay_get_license()->is_pro( 'professional', '>=' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'A Professional license or higher is required to process refunds.', 'stripe' ) )
			);
		}

		// Validate params.
		$transaction_id = isset( $_POST['transaction_id'] ) ? absint( $_POST['transaction_id'] ) : 0;
		$amount         = isset( $_POST['amount'] ) ? floatval( $_POST['amount'] ) : 0;

		if ( empty( $transaction_id ) || $amount <= 0 ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid transaction or amount.', 'stripe' ) )
			);
		}

		// Fetch transaction, scoped to the connected account like every other
		// read on this page -- a refund is the one write that takes an ID from
		// the request, so it must not reach past that boundary either (#3533).
		global $wpdb;

		$account_scope = AccountScope::get_where_fragment();

		/** @var Transaction_Row|null $transaction */
		$transaction = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $account_scope is itself a prepared fragment; only the table name is interpolated.
				"SELECT * FROM {$wpdb->prefix}wpsp_transactions WHERE id = %d{$account_scope}",
				$transaction_id
			)
		);

		if ( ! $transaction ) {
			wp_send_json_error(
				array( 'message' => __( 'Transaction not found.', 'stripe' ) )
			);
		}

		// Validate refundable state.
		if ( 'payment_intent' !== $transaction->object ) {
			wp_send_json_error(
				array( 'message' => __( 'Only payment intent transactions can be refunded.', 'stripe' ) )
			);
		}

		if ( ! in_array( $transaction->status, array( 'succeeded', 'refunded' ), true ) ) {
			wp_send_json_error(
				array( 'message' => __( 'This transaction cannot be refunded in its current status.', 'stripe' ) )
			);
		}

		$amount_total    = absint( $transaction->amount_total );
		$amount_refunded = absint( $transaction->amount_refunded );
		$remaining       = $amount_total - $amount_refunded;

		if ( $remaining <= 0 ) {
			wp_send_json_error(
				array( 'message' => __( 'This transaction has already been fully refunded.', 'stripe' ) )
			);
		}

		// Convert display amount to Stripe smallest-unit amount.
		$is_zero_decimal = simpay_is_zero_decimal( $transaction->currency );
		$refund_amount   = $is_zero_decimal ? intval( $amount ) : intval( round( $amount * 100 ) );

		if ( $refund_amount > $remaining ) {
			wp_send_json_error(
				array(
					'message' => __( 'Refund amount exceeds the remaining refundable amount.', 'stripe' ),
				)
			);
		}

		// Resolve API key based on transaction livemode.
		$api_key = $transaction->livemode
			? simpay_get_setting( 'live_secret_key', '' )
			: simpay_get_setting( 'test_secret_key', '' );

		if ( empty( $api_key ) ) {
			wp_send_json_error(
				array(
					'message' => sprintf(
						/* translators: %s: "live" or "test" */
						__( 'No %s secret key configured.', 'stripe' ),
						$transaction->livemode ? __( 'live', 'stripe' ) : __( 'test', 'stripe' )
					),
				)
			);
		}

		// Create the refund via Stripe.
		try {
			$refund = \SimplePay\Core\API\Refunds\create(
				array(
					'payment_intent' => $transaction->_object_id,
					'amount'         => $refund_amount,
				),
				array(
					'api_key' => $api_key,
				)
			);
		} catch ( \SimplePay\Vendor\Stripe\Exception\ApiErrorException $e ) {
			wp_send_json_error(
				array( 'message' => $e->getMessage() )
			);
		} catch ( \Exception $e ) {
			wp_send_json_error(
				array( 'message' => $e->getMessage() )
			);
		}

		// Update local DB.
		//
		// Both full and partial refunds are stored as 'refunded'; a partial
		// refund is detected at display time by comparing amount_refunded to
		// amount_total (see get_display_status() and the detail view). This
		// matches how the webhook refund flow records refunds.
		$new_amount_refunded = $amount_refunded + $refund_amount;
		$new_status          = 'refunded';

		$wpdb->update(
			$wpdb->prefix . 'wpsp_transactions',
			array(
				'amount_refunded' => $new_amount_refunded,
				'status'          => $new_status,
				'date_modified'   => current_time( 'mysql', true ),
			),
			array( 'id' => $transaction_id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);

		// Format amounts for response.
		$display_refunded  = simpay_format_currency( $new_amount_refunded, $transaction->currency );
		$display_remaining = simpay_format_currency(
			$amount_total - $new_amount_refunded,
			$transaction->currency
		);

		wp_send_json_success(
			array(
				'message'           => __( 'Refund processed successfully.', 'stripe' ),
				'refund_id'         => $refund->id,
				'amount_refunded'   => $new_amount_refunded,
				'display_refunded'  => $display_refunded,
				'display_remaining' => $display_remaining,
				'fully_refunded'    => $new_amount_refunded >= $amount_total,
			)
		);
	}
}
