<?php

namespace SimplePay\Vendor\Stripe\V2;

/**
 * @property string $id Unique identifier for the event.
 * @property string $object String representing the object's type. Objects of the same type share the same value of the object field.
 * @property int $created Time at which the object was created.
 * @property \SimplePay\Vendor\Stripe\StripeObject $reason Reason for the event.
 * @property string $type The type of the event.
 * @property null|string $context The SimplePay\Vendor\Stripe account of the event
 */
abstract class Event extends \SimplePay\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'v2.core.event';
}
