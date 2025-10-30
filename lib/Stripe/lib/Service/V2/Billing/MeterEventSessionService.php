<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\V2\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class MeterEventSessionService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates a meter event session to send usage on the high-throughput meter event
     * stream. Authentication tokens are only valid for 15 minutes, so you will need to
     * create a new meter event session when your token expires.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\V2\Billing\MeterEventSession
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v2/billing/meter_event_session', $params, $opts);
    }
}
