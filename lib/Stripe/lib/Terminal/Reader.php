<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Terminal;

/**
 * A Reader represents a physical device for accepting payment details.
 *
 * Related guide: <a
 * href="https://stripe.com/docs/terminal/readers/connecting">Connecting to a
 * Reader</a>.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string $device_sw_version The current software version of the reader.
 * @property string $device_type Type of reader, one of <code>bbpos_chipper2x</code> or <code>verifone_P400</code>.
 * @property null|string $ip_address The local IP address of the reader.
 * @property string $label Custom label given to the reader for easier identification.
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|string|\SimplePay\Vendor\Stripe\Terminal\Location $location The location identifier of the reader.
 * @property \SimplePay\Vendor\Stripe\StripeObject $metadata Set of <a href="https://stripe.com/docs/api/metadata">key-value pairs</a> that you can attach to an object. This can be useful for storing additional information about the object in a structured format.
 * @property string $serial_number Serial number of the reader.
 * @property null|string $status The networking status of the reader.
 */
class Reader extends \SimplePay\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.reader';

    use \SimplePay\Vendor\Stripe\ApiOperations\All;
    use \SimplePay\Vendor\Stripe\ApiOperations\Create;
    use \SimplePay\Vendor\Stripe\ApiOperations\Delete;
    use \SimplePay\Vendor\Stripe\ApiOperations\Retrieve;
    use \SimplePay\Vendor\Stripe\ApiOperations\Update;
}
