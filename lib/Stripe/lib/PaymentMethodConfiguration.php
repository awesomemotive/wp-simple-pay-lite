<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe;

/**
 * PaymentMethodConfigurations control which payment methods are displayed to your customers when you don't explicitly specify payment method types. You can have multiple configurations with different sets of payment methods for different scenarios.
 *
 * There are two types of PaymentMethodConfigurations. Which is used depends on the <a href="https://stripe.com/docs/connect/charges">charge type</a>:
 *
 * <strong>Direct</strong> configurations apply to payments created on your account, including Connect destination charges, Connect separate charges and transfers, and payments not involving Connect.
 *
 * <strong>Child</strong> configurations apply to payments created on your connected accounts using direct charges, and charges with the on_behalf_of parameter.
 *
 * Child configurations have a <code>parent</code> that sets default values and controls which settings connected accounts may override. You can specify a parent ID at payment time, and SimplePay\Vendor\Stripe will automatically resolve the connected account’s associated child configuration. Parent configurations are <a href="https://dashboard.stripe.com/settings/payment_methods/connected_accounts">managed in the dashboard</a> and are not available in this API.
 *
 * Related guides:
 * - <a href="https://stripe.com/docs/connect/payment-method-configurations">Payment Method Configurations API</a>
 * - <a href="https://stripe.com/docs/payments/multiple-payment-method-configs">Multiple configurations on dynamic payment methods</a>
 * - <a href="https://stripe.com/docs/connect/multiple-payment-method-configurations">Multiple configurations for your Connect accounts</a>
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $acss_debit
 * @property bool $active Whether the configuration can be used for new payments.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $affirm
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $afterpay_clearpay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $alipay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $apple_pay
 * @property null|string $application For child configs, the Connect application associated with the configuration.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $au_becs_debit
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $bacs_debit
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $bancontact
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $blik
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $boleto
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $card
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $cartes_bancaires
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $cashapp
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $eps
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $fpx
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $giropay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $google_pay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $grabpay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $id_bank_transfer
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $ideal
 * @property bool $is_default The default configuration is used whenever a payment method configuration is not specified.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $jcb
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $klarna
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $konbini
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $multibanco
 * @property string $name The configuration's name.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $netbanking
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $oxxo
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $p24
 * @property null|string $parent For child configs, the configuration's parent configuration.
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $pay_by_bank
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $paynow
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $paypal
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $promptpay
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $sepa_debit
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $sofort
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $upi
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $us_bank_account
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $wechat_pay
 */
class PaymentMethodConfiguration extends ApiResource
{
    const OBJECT_NAME = 'payment_method_configuration';

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;
}
