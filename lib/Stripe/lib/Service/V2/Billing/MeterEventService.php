<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\V2\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class MeterEventService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a meter event. Events are validated synchronously, but are processed
     * asynchronously. Supports up to 1,000 events per second in livemode. For higher
     * rate-limits, please use meter event streams instead.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\V2\Billing\MeterEvent
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v2/billing/meter_events', $params, $opts);
    }
}
