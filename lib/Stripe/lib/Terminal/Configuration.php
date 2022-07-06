<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Terminal;

/**
 * A Configurations object represents how features should be configured for
 * terminal readers.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property \SimplePay\Vendor\Stripe\StripeObject $bbpos_wisepos_e
 * @property null|bool $is_account_default Whether this Configuration is the default for your account
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property \SimplePay\Vendor\Stripe\StripeObject $tipping
 * @property \SimplePay\Vendor\Stripe\StripeObject $verifone_p400
 */
class Configuration extends \SimplePay\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.configuration';

    use \SimplePay\Vendor\Stripe\ApiOperations\All;
    use \SimplePay\Vendor\Stripe\ApiOperations\Create;
    use \SimplePay\Vendor\Stripe\ApiOperations\Delete;
    use \SimplePay\Vendor\Stripe\ApiOperations\Retrieve;
    use \SimplePay\Vendor\Stripe\ApiOperations\Update;
}
