<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service;

class QuotePhaseService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of quote phases.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\QuotePhase>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/quote_phases', $params, $opts);
    }

    /**
     * When retrieving a quote phase, there is an includable
     * <strong>line_items</strong> property containing the first handful of those
     * items. There is also a URL where you can retrieve the full (paginated) list of
     * line items.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\LineItem>
     */
    public function allLineItems($id, $params = null, $opts = null)
    {
        return $this->requestCollection('get', $this->buildPath('/v1/quote_phases/%s/line_items', $id), $params, $opts);
    }

    /**
     * Retrieves the quote phase with the given ID.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\QuotePhase
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/quote_phases/%s', $id), $params, $opts);
    }
}
