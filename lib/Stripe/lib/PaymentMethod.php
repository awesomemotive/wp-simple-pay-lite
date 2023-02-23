<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe;

/**
 * PaymentMethod objects represent your customer's payment instruments. You can use
 * them with <a
 * href="https://stripe.com/docs/payments/payment-intents">PaymentIntents</a> to
 * collect payments or save them to Customer objects to store instrument details
 * for future payments.
 *
 * Related guides: <a
 * href="https://stripe.com/docs/payments/payment-methods">Payment Methods</a> and
 * <a href="https://stripe.com/docs/payments/more-payment-scenarios">More Payment
 * Scenarios</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimplePay\Vendor\Stripe\StripeObject $acss_debit
 * @property \SimplePay\Vendor\Stripe\StripeObject $affirm
 * @property \SimplePay\Vendor\Stripe\StripeObject $afterpay_clearpay
 * @property \SimplePay\Vendor\Stripe\StripeObject $alipay
 * @property \SimplePay\Vendor\Stripe\StripeObject $au_becs_debit
 * @property \SimplePay\Vendor\Stripe\StripeObject $bacs_debit
 * @property \SimplePay\Vendor\Stripe\StripeObject $bancontact
 * @property \SimplePay\Vendor\Stripe\StripeObject $billing_details
 * @property \SimplePay\Vendor\Stripe\StripeObject $blik
 * @property \SimplePay\Vendor\Stripe\StripeObject $boleto
 * @property \SimplePay\Vendor\Stripe\StripeObject $card
 * @property \SimplePay\Vendor\Stripe\StripeObject $card_present
 * @property \SimplePay\Vendor\Stripe\StripeObject $cashapp
 * @property int $created Time at which the object was created. Measured in seconds since the Unix epoch.
 * @property null|string|\SimplePay\Vendor\Stripe\Customer $customer The ID of the Customer to which this PaymentMethod is saved. This will not be set when the PaymentMethod has not been saved to a Customer.
 * @property \SimplePay\Vendor\Stripe\StripeObject $customer_balance
 * @property \SimplePay\Vendor\Stripe\StripeObject $eps
 * @property \SimplePay\Vendor\Stripe\StripeObject $fpx
 * @property \SimplePay\Vendor\Stripe\StripeObject $giropay
 * @property \SimplePay\Vendor\Stripe\StripeObject $grabpay
 * @property \SimplePay\Vendor\Stripe\StripeObject $ideal
 * @property \SimplePay\Vendor\Stripe\StripeObject $interac_present
 * @property \SimplePay\Vendor\Stripe\StripeObject $klarna
 * @property \SimplePay\Vendor\Stripe\StripeObject $konbini
 * @property \SimplePay\Vendor\Stripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property \SimplePay\Vendor\Stripe\StripeObject $oxxo
 * @property \SimplePay\Vendor\Stripe\StripeObject $p24
 * @property \SimplePay\Vendor\Stripe\StripeObject $paynow
 * @property \SimplePay\Vendor\Stripe\StripeObject $paypal
 * @property \SimplePay\Vendor\Stripe\StripeObject $pix
 * @property \SimplePay\Vendor\Stripe\StripeObject $promptpay
 * @property \SimplePay\Vendor\Stripe\StripeObject $radar_options Options to configure Radar. See <a href="https://stripe.com/docs/radar/radar-session">Radar Session</a> for more information.
 * @property \SimplePay\Vendor\Stripe\StripeObject $sepa_debit
 * @property \SimplePay\Vendor\Stripe\StripeObject $sofort
 * @property string $type The type of the PaymentMethod. An additional hash is included on the PaymentMethod with a name matching this value. It contains additional information specific to the PaymentMethod type.
 * @property \SimplePay\Vendor\Stripe\StripeObject $us_bank_account
 * @property \SimplePay\Vendor\Stripe\StripeObject $wechat_pay
 * @property \SimplePay\Vendor\Stripe\StripeObject $zip
 */
class PaymentMethod extends ApiResource
{
    const OBJECT_NAME = 'payment_method';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\PaymentMethod the attached payment method
     */
    public function attach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/attach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }

    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\PaymentMethod the detached payment method
     */
    public function detach($params = null, $opts = null)
    {
        $url = $this->instanceUrl() . '/detach';
        list($response, $opts) = $this->_request('post', $url, $params, $opts);
        $this->refreshFrom($response, $opts);

        return $this;
    }
}
