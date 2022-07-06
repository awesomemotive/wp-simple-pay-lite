<?php
/**
 * Payment Form: Price Options
 *
 * @package SimplePay\Core\PaymentForm
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.1.0
 */

namespace SimplePay\Core\PaymentForm;

use SimplePay\Core\API;
use Exception;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PriceOptions class.
 *
 * @since 4.1.0
 */
class PriceOptions {

	/**
	 * Price options Payment Form.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Core\Abstracts\Form
	 */
	private $form;

	/**
	 * Price options.
	 *
	 * @since 4.1.0
	 * @var \SimplePay\Core\PaymentForm\PriceOption[]
	 */
	private $prices;

	/**
	 * Constructs PriceOptions.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form
	 */
	public function __construct( $form ) {
		$this->form = $form;
	}

	/**
	 * Returns a list of price options.
	 *
	 * @since 4.1.0
	 *
	 * @return \SimplePay\Core\PaymentForm\PriceOption[]
	 */
	public function get_prices() {
		if ( null !== $this->prices ) {
			return $this->prices;
		}

		// Do not create price options if the form is still an auto-draft.
		if ( 'auto-draft' === $this->form->post->post_status ) {
			return array();
		}

		// Current mode has not been modified the most recently.
		if ( false === $this->is_current_mode_latest() ) {
			$prices = $this->sync();

			// Current mode is considered the latest.
		} else {
			$prices = $this->get_current_mode_prices();
		}

		$price_options = array();

		foreach ( $prices as $instance_id => $price_data ) {
			try {
				$price_options[ $instance_id ] = new PriceOption(
					$price_data,
					$this->form
				);
			} catch ( Exception $e ) {
				continue;
			}
		}

		$this->prices = $price_options;

		return $this->prices;
	}

	/**
	 * Syncs price options to the current payment mode.
	 *
	 * This should only occur once the current mode is determined to be out
	 * of date.
	 *
	 * @since 4.1.0
	 *
	 * @return array Updated price options for the current mode.
	 */
	public function sync() {
		// API keys are required on both sides. If they are missing, nothing
		// can be done.
		if ( false === $this->can_sync() ) {
			return array();
		}

		$current_prices = $this->get_current_mode_prices();
		$alt_prices     = $this->get_alt_mode_prices();

		// Prices in current mode are empty, but not alternative. Copy.
		if ( empty( $current_prices ) && ! empty( $alt_prices ) ) {
			$prices = $this->copy_alt_mode_prices();

			// Prices exist in current mode. Sync with alternative mode.
		} else {
			$prices = $this->sync_alt_mode_prices();
		}

		// Persist copied prices.
		update_post_meta(
			$this->form->id,
			$this->get_current_mode_prices_storage_key(),
			$prices
		);

		update_post_meta(
			$this->form->id,
			$this->get_current_mode_prices_modified_storage_key(),
			time()
		);

		return $prices;
	}

	/**
	 * Determines if price options can be synced. Requires both mode's API
	 * keys to be available.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	private function can_sync() {
		$live_secret = ! empty(
			simpay_get_filtered(
				'secret_key',
				simpay_get_setting( 'live_secret_key', '' ),
				$this->form->id
			)
		);

		$test_secret = ! empty(
			simpay_get_filtered(
				'secret_key',
				simpay_get_setting( 'test_secret_key', '' ),
				$this->form->id
			)
		);

		return ! empty( $live_secret ) && ! empty( $test_secret );
	}

	/**
	 * Copies price options from an alternative mode when the current mode is
	 * empty.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] Price options.
	 */
	private function copy_alt_mode_prices() {
		// Retrieve, or create, a container Product for the current mode.
		$product = $this->get_current_mode_product_id();

		if ( false === $product ) {
			return array();
		}

		$alt_mode_prices           = $this->get_alt_mode_prices();
		$alt_mode_api_request_args = $this->get_alt_mode_api_request_args();

		$current_mode_api_request_args =
			$this->get_current_mode_api_request_args();

		$copied_prices = array();

		// Loop through existing alternative mode prices and copy to current mode.
		foreach ( $alt_mode_prices as $instance_id => $price ) {
			$is_defined_price = simpay_payment_form_prices_is_defined_price(
				$price['id']
			);

			if ( true === $is_defined_price ) {
				try {
					// Main defined amount.
					$alt_price = API\Prices\retrieve(
						$price['id'],
						$alt_mode_api_request_args
					);

					$new_price = API\Prices\create(
						$this->get_price_args_to_copy( $alt_price, $product ),
						$current_mode_api_request_args
					);

					// Update ID with newly created object.
					$price['id'] = $new_price->id;

					// Recurring defined amount.
					if (
						isset( $price['recurring'] ) &&
						isset( $price['recurring']['id'] )
					) {
						$alt_recurring_price = API\Prices\retrieve(
							$price['recurring']['id'],
							$alt_mode_api_request_args
						);

						$new_recurring_price = API\Prices\create(
							$this->get_price_args_to_copy( $alt_recurring_price, $product ),
							$current_mode_api_request_args
						);

						// Update ID with newly created object.
						$price['recurring']['id'] = $new_recurring_price->id;
					}
				} catch ( Exception $e ) {
					continue;
				}
			}

			$copied_prices[ $instance_id ] = $price;
		}

		return $copied_prices;
	}

	/**
	 * Synces price option data from the alternative mode to the current mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[]
	 */
	private function sync_alt_mode_prices() {
		// Retrieve, or create, a container Product for the current mode.
		$product = $this->get_current_mode_product_id();

		if ( false === $product ) {
			return array();
		}

		$alt_mode_prices     = $this->get_alt_mode_prices();
		$current_mode_prices = $this->get_current_mode_prices();

		$alt_mode_api_request_args =
			$this->get_alt_mode_api_request_args();

		$current_mode_api_request_args =
			$this->get_current_mode_api_request_args();

		$synced_prices = array();

		foreach ( $alt_mode_prices as $instance_id => $price ) {

			// Price already exists in the current mode, merge non-ID arguments.
			if ( isset( $current_mode_prices[ $instance_id ] ) ) {
				// Remove mode-specific IDs from arguments.
				$new_args = $price;
				unset( $new_args['id'] );
				unset( $new_args['recurring']['id'] );

				// Gather current mode IDs.
				$existing_args = array(
					'id' => $current_mode_prices[ $instance_id ]['id'],
				);

				if (
					isset( $current_mode_prices[ $instance_id ]['recurring'] ) &&
					isset( $current_mode_prices[ $instance_id ]['recurring']['id'] )
				) {
					$existing_args['recurring']['id'] =
						$current_mode_prices[ $instance_id ]['recurring']['id'];
				}

				// Merge alternative mode arguments and current mode IDs.
				$merged_price = array_merge_recursive(
					$new_args,
					$existing_args
				);

				$synced_prices[ $instance_id ] = $merged_price;

				// Price does not exist in current mode, create it.
			} else {
				$price_data = $price;
				unset( $price_data['id'] );
				unset( $price_data['recurring']['id'] );

				$is_defined_price = simpay_payment_form_prices_is_defined_price(
					$price['id']
				);

				if ( true === $is_defined_price ) {
					// Main defined amount.
					$alt_price = API\Prices\retrieve(
						$price['id'],
						$alt_mode_api_request_args
					);

					$new_price = API\Prices\create(
						$this->get_price_args_to_copy( $alt_price, $product ),
						$current_mode_api_request_args
					);

					// Update ID with newly created object.
					$price_data['id'] = $new_price->id;
				}

				// Recurring defined amount.
				if (
					isset( $price['recurring'] ) &&
					isset( $price['recurring']['id'] )
				) {
					$alt_recurring_price = API\Prices\retrieve(
						$price['recurring']['id'],
						$alt_mode_api_request_args
					);

					$new_recurring_price = API\Prices\create(
						$this->get_price_args_to_copy( $alt_recurring_price, $product ),
						$current_mode_api_request_args
					);

					// Update ID with newly created object.
					$price_data['recurring']['id'] = $new_recurring_price->id;
				}

				$synced_prices[ $instance_id ] = $price_data;
			}
		}

		// Sort by order of alternative mode's prices.
		$synced_prices = array_replace(
			array_flip( array_keys( $alt_mode_prices ) ),
			$synced_prices
		);

		return $synced_prices;
	}

	/**
	 * Returns the storage key for the current mode's prices.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_current_mode_prices_storage_key() {
		return $this->form->is_livemode()
			? '_simpay_prices_live'
			: '_simpay_prices_test';
	}

	/**
	 * Returns the storage key for the current mode's price modification time.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_current_mode_prices_modified_storage_key() {
		return $this->form->is_livemode()
			? '_simpay_prices_live_modified'
			: '_simpay_prices_test_modified';
	}

	/**
	 * Returns API request arguments for the current payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array
	 */
	private function get_current_mode_api_request_args() {
		return $this->form->get_api_request_args(
			array(
				'livemode' => $this->form->is_livemode(),
			)
		);
	}

	/**
	 * Retrieves saved price option data for the current payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] List of price option data. Empty if no price options are
	 *                 available in the current mode.
	 */
	private function get_current_mode_prices() {
		$prices = get_post_meta(
			$this->form->id,
			$this->get_current_mode_prices_storage_key(),
			true
		);

		if ( '' === $prices ) {
			return array();
		}

		return $prices;
	}

	/**
	 * Returns the storage key for the alternative mode's prices.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_alt_mode_prices_storage_key() {
		return $this->form->is_livemode()
			? '_simpay_prices_test'
			: '_simpay_prices_live';
	}

	/**
	 * Returns the storage key for the alternative mode's price modification time.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_alt_mode_prices_modified_storage_key() {
		return $this->form->is_livemode()
			? '_simpay_prices_test_modified'
			: '_simpay_prices_live_modified';
	}

	/**
	 * Returns API request arguments for the alternative payment mode.
	 *
	 * @return array
	 */
	private function get_alt_mode_api_request_args() {
		return $this->form->get_api_request_args(
			array(
				'livemode' => ! $this->form->is_livemode(),
			)
		);
	}

	/**
	 * Retrieves saved price option data for the alternative payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @return array[] List of price option data. Empty if no price options are
	 *                 available in the current mode.
	 */
	private function get_alt_mode_prices() {
		$prices = get_post_meta(
			$this->form->id,
			$this->get_alt_mode_prices_storage_key(),
			true
		);

		if ( '' === $prices ) {
			return array();
		}

		return $prices;
	}

	/**
	 * Determines if the prices in the current payment mode should be considered
	 * the most up to date.
	 *
	 * @since 4.1.0
	 *
	 * @return bool
	 */
	private function is_current_mode_latest() {
		$current_mode_modified = get_post_meta(
			$this->form->id,
			$this->get_current_mode_prices_modified_storage_key(),
			true
		);

		$alt_mode_modified = get_post_meta(
			$this->form->id,
			$this->get_alt_mode_prices_modified_storage_key(),
			true
		);

		// Current mode has not been modified, it is not the latest.
		if ( empty( $current_mode_modified ) ) {
			return false;
		}

		// Alt mode has not been modified, fall back to current as latest.
		if ( empty( $alt_mode_modified ) ) {
			return true;
		}

		// Determine if the current mode has been modified more recently.
		return $current_mode_modified > $alt_mode_modified;
	}

	/**
	 * Returns a list of Price arguments that should be copied.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Vendor\Stripe\Price $price Existing Price.
	 * @param string                         $product_id Existing Product ID.
	 * @return array
	 */
	private function get_price_args_to_copy( $price, $product_id ) {
		$args = array(
			'unit_amount' => $price->unit_amount,
			'currency'    => $price->currency,
			'product'     => $product_id,
		);

		if ( isset( $price->metadata ) ) {
			$args['metadata'] = $price->metadata->toArray();
		}

		if ( isset( $price->recurring ) ) {
			$args['recurring'] = $price->recurring->toArray();
		}

		return $args;
	}

	/**
	 * Returns the storage key for the current mode's container Product ID.
	 *
	 * @since 4.1.0
	 *
	 * @return string
	 */
	private function get_current_mode_product_storage_key() {
		return $this->form->is_livemode()
			? '_simpay_product_live'
			: '_simpay_product_test';
	}

	/**
	 * Returns the current mode's container Product ID.
	 *
	 * @since 4.1.0
	 *
	 * @return void
	 */
	private function get_current_mode_product_id() {
		$product_id = get_post_meta(
			$this->form->id,
			$this->get_current_mode_product_storage_key(),
			true
		);

		if ( ! empty( $product_id ) ) {
			return $product_id;
		}

		// Create a Product if one was not found.
		try {
			$product = API\Products\create(
				$this->get_product_args( $this->form ),
				$this->get_current_mode_api_request_args()
			);

			// Persist Product ID.
			update_post_meta(
				$this->form->id,
				$this->get_current_mode_product_storage_key(),
				$product->id
			);

			return $product->id;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Returns arguments used to create a container Product for the current
	 * payment mode.
	 *
	 * @since 4.1.0
	 *
	 * @param \SimplePay\Core\Abstracts\Form $form Payment Form.
	 * @return array
	 */
	private function get_product_args( $form ) {
		$product_args = array();

		// Name.
		$title = get_post_meta( $this->form->id, '_company_name', true );
		$name  = ! empty( $title ) ? $title : get_bloginfo( 'name' );

		// https://github.com/wpsimplepay/wp-simple-pay-pro/issues/1598
		if ( empty( $name ) ) {
			$name = sprintf(
				/* translators: %d payment form ID. */
				__( 'WP Simple Pay - Form %d', 'stripe' ),
				$form->id
			);
		}

		$product_args['name'] = esc_html( $name );

		// Description. Optional.
		$description = get_post_meta( $this->form->id, '_item_description', true );

		if ( ! empty( $description ) ) {
			$product_args['description'] = esc_html( $description );
		}

		// Images. Optional.
		if ( ! empty( $this->form->image_url ) ) {
			$product_args['images'] = array( $this->form->image_url );
		}

		return $product_args;
	}

}
