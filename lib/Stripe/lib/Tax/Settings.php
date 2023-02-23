<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Tax;

/**
 * You can use Tax <code>Settings</code> to manage configurations used by SimplePay\Vendor\Stripe
 * Tax calculations.
 *
 * Related guide: <a href="https://stripe.com/docs/tax/connect/settings">Account
 * settings</a>.
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimplePay\Vendor\Stripe\StripeObject $defaults
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property \SimplePay\Vendor\Stripe\StripeObject[] $locations The places where your business is located.
 */
class Settings extends \SimplePay\Vendor\Stripe\SingletonApiResource
{
    const OBJECT_NAME = 'tax.settings';

    use \SimplePay\Vendor\Stripe\ApiOperations\SingletonRetrieve;
    use \SimplePay\Vendor\Stripe\ApiOperations\Update;
}
