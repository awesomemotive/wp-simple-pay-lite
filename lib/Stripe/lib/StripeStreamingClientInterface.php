<?php

namespace SimplePay\Vendor\Stripe;

/**
 * Interface for a SimplePay\Vendor\Stripe client.
 */
interface StripeStreamingClientInterface extends BaseStripeClientInterface
{
    public function requestStream($method, $path, $readBodyChunkCallable, $params, $opts);
}
