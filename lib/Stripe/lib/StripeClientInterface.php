<?php

namespace SimplePay\Vendor\Stripe;

/**
 * Interface for a SimplePay\Vendor\Stripe client.
 */
interface StripeClientInterface extends BaseStripeClientInterface
{
    /**
     * Sends a request to Stripe's API.
     *
     * @param string $method the HTTP method
     * @param string $path the path of the request
     * @param array $params the parameters of the request
     * @param array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts the special modifiers of the request
     *
     * @return \SimplePay\Vendor\Stripe\StripeObject the object returned by Stripe's API
     */
    public function request($method, $path, $params, $opts);
}
