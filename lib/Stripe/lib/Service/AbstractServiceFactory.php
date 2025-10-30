<?php

namespace SimplePay\Vendor\Stripe\Service;

/**
 * Abstract base class for all service factories used to expose service
 * instances through {@link \SimplePay\Vendor\Stripe\StripeClient}.
 *
 * Service factories serve two purposes:
 *
 * 1. Expose properties for all services through the `__get()` magic method.
 * 2. Lazily initialize each service instance the first time the property for
 *    a given service is used.
 */
abstract class AbstractServiceFactory
{
    use ServiceNavigatorTrait;

    /**
     * @param \SimplePay\Vendor\Stripe\StripeClientInterface $client
     */
    public function __construct($client)
    {
        $this->client = $client;
    }
}
