<?php
/**
 * Telemetry: Payment forms
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.7.10
 */

namespace SimplePay\Core\CustomerSuccess\TelemetryData;

/**
 * PaymentFormTelemetryData class.
 *
 * @since 4.7.10
 */
class PaymentFormTelemetryData extends AbstractTelemetryData {

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		return array_merge(
			$this->get_form_types(),
			$this->get_tax_types(),
			array(
				'payment_pages' => $this->get_payment_pages(),
				'inventory'     => $this->get_inventory(),
				'scheduling'    => $this->get_scheduling(),
			)
		);
	}

	/**
	 * Returns the number of times a particular payment form type has been used across all forms.
	 *
	 * @since 4.7.10
	 *
	 * @return array<string, int>
	 */
	private function get_form_types() {
		global $wpdb;

		$display_types = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
				'_form_display_type'
			)
		);

		$counts = array();

		foreach ( $display_types as $display_type ) {
			switch ( $display_type ) {
				case 'embedded':
					$display_type = 'type_on_site';
					break;
				default:
					$display_type = 'type_on_site';
					break;
			}

			if ( ! isset( $counts[ $display_type ] ) ) {
				$counts[ $display_type ] = 0;
			}

			$counts[ $display_type ]++;
		}

		return $counts;
	}

	/**
	 * Returns the number of times inventory is used across all forms.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_inventory() {
		global $wpdb;

		$enabled = $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_inventory' AND meta_value = 'yes'"
		);

		return $enabled ? (int) $enabled : 0;
	}

	/**
	 * Returns the number of times scheudling is used across all forms.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_scheduling() {
		global $wpdb;

		$enabled = $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key IN('_schedule_start', '_schedule_end') AND meta_value = 'yes'"
		);

		return $enabled ? (int) $enabled : 0;
	}

	/**
	 * Returns the number of times tax collection types have been enabled across all forms.
	 *
	 * @since 4.7.10
	 *
	 * @return array<string, int>
	 */
	private function get_tax_types() {
		global $wpdb;

		$tax_meta = $wpdb->get_col(
			"SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = '_tax_status'"
		);

		$taxes = array();

		foreach ( $tax_meta as $tax_type ) {
			switch ( $tax_type ) {
				case 'none':
				case 'automatic':
					$tax_type = 'tax_' . $tax_type;
					break;
				default:
					$tax_type = 'tax_global';
			}

			if ( ! isset( $taxes[ $tax_type ] ) ) {
				$taxes[ $tax_type ] = 0;
			}

			$taxes[ $tax_type ]++;
		}

		return $taxes;
	}

	/**
	 * Returns the number of times payment pages across all forms.
	 *
	 * @since 4.7.10
	 *
	 * @return int
	 */
	private function get_payment_pages() {
		global $wpdb;

		$enabled = $wpdb->get_var(
			"SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = '_enable_payment_page' AND meta_value = 'yes'"
		);

		return $enabled ? (int) $enabled : 0;
	}
}
