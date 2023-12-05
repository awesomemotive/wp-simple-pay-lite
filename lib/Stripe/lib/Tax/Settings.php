<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Tax;

/**
 * You can use Tax <code>Settings</code> to manage configurations used by SimplePay\Vendor\Stripe Tax calculations.
 *
 * Related guide: <a href="https://stripe.com/docs/tax/settings-api">Using the Settings API</a>
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimplePay\Vendor\Stripe\StripeObject $defaults
 * @property null|\SimplePay\Vendor\Stripe\StripeObject $head_office The place where your business is located.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property string $status The <code>active</code> status indicates you have all required settings to calculate tax. A status can transition out of <code>active</code> when new required settings are introduced.
 * @property \SimplePay\Vendor\Stripe\StripeObject $status_details
 */
class Settings extends \SimplePay\Vendor\Stripe\SingletonApiResource
{
    const OBJECT_NAME = 'tax.settings';

    use \SimplePay\Vendor\Stripe\ApiOperations\SingletonRetrieve;
    use \SimplePay\Vendor\Stripe\ApiOperations\Update;

    const STATUS_ACTIVE = 'active';
    const STATUS_PENDING = 'pending';
}
