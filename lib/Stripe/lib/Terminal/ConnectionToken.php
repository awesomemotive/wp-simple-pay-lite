<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Terminal;

/**
 * A Connection Token is used by the SimplePay\Vendor\Stripe Terminal SDK to connect to a reader.
 *
 * Related guide: <a href="https://stripe.com/docs/terminal/fleet/locations">Fleet management</a>
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|string $location The id of the location that this connection token is scoped to. Note that location scoping only applies to internet-connected readers. For more details, see <a href="https://docs.stripe.com/terminal/fleet/locations-and-zones?dashboard-or-api=api#connection-tokens">the docs on scoping connection tokens</a>.
 * @property string $secret Your application should pass this token to the SimplePay\Vendor\Stripe Terminal SDK.
 */
class ConnectionToken extends \SimplePay\Vendor\Stripe\ApiResource
{
    const OBJECT_NAME = 'terminal.connection_token';

    /**
     * To connect to a reader the SimplePay\Vendor\Stripe Terminal SDK needs to retrieve a short-lived
     * connection token from Stripe, proxied through your server. On your backend, add
     * an endpoint that creates and returns a connection token.
     *
     * @param null|array $params
     * @param null|array|string $options
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Terminal\ConnectionToken the created resource
     */
    public static function create($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \SimplePay\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }
}
