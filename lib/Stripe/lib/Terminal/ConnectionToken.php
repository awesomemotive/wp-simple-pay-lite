<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Terminal;

/**
 * A Connection Token is used by the SimplePay\Vendor\Stripe Terminal SDK to connect to a reader.
 *
 * Related guide: <a href="https://stripe.com/docs/terminal/fleet/locations">Fleet
 * Management</a>.
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property string $location The id of the location that this connection token is scoped to. Note that location scoping only applies to internet-connected readers. For more details, see <a href="https://stripe.com/docs/terminal/fleet/locations#connection-tokens">the docs on scoping connection tokens</a>.
 * @property string $secret Your application should pass this token to the SimplePay\Vendor\Stripe Terminal SDK.
 */
class ConnectionToken extends \SimplePay\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.connection_token';

    use \SimplePay\Vendor\Stripe\ApiOperations\Create;
}
