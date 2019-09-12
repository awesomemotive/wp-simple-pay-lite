<?php
/**
 * Backwards compatibility for <= 3.5.x Payment class.
 *
 * Namespace remains the same in case someone was referencing it directly.
 *
 * @since 3.6.0
 */

namespace SimplePay\Core\Payments;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Payment {
	public $token = null;
	public $email = null;

	public $customer                = null;
	public $customer_id             = null;
	public $charge                  = null;
	public $form_id                 = null;
	public $amount                  = 0;
	public $description             = null;
	public $company_name            = null;
	public $currency                = null;
	public $locale                  = null;
	public $test_mode               = null;
	public $metadata                = array();
	public $decimal_places          = '';
	public $form                    = null;
	public $success                 = false;
	public $invoice                 = '';
	public $has_quantity            = false;
	public $quantity                = null;
	public $subscription            = null;
	public $recurring_amount_toggle = false;

	/**
	 * Setup basic form properties.
	 *
	 * @since 3.6.0
	 *
	 * @param SimplePay\Core\Abstracts\Form $form Form instance.
	 */
	public function __construct( $form ) {
		$this->currency         = $form->currency;
		$this->company_name     = $form->company_name;
		$this->item_description = $form->item_description;
		$this->locale           = $form->locale;
		$this->decimal_places   = simpay_get_decimal_places();

		/**
		 * Allow additional attributes to be set.
		 *
		 * @since unknown
		 */
		do_action( 'simpay_payment_attributes', $this );
	}
}
