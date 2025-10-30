<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Tax;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class CalculationService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieves the line items of a tax calculation as a collection, if the
     * calculation hasn’t expired.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Tax\CalculationLineItem>
     */
    public function allLineItems($id, $params = null, $opts = null)
    {
        return $this->requestCollection('get', $this->buildPath('/v1/tax/calculations/%s/line_items', $id), $params, $opts);
    }

    /**
     * Calculates tax based on the input and returns a Tax <code>Calculation</code>
     * object.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Tax\Calculation
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/tax/calculations', $params, $opts);
    }

    /**
     * Retrieves a Tax <code>Calculation</code> object, if the calculation hasn’t
     * expired.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Tax\Calculation
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/tax/calculations/%s', $id), $params, $opts);
    }
}
