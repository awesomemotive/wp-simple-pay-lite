<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Capital;

class FinancingTransactionService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of financing transactions. The transactions are returned in
     * sorted order, with the most recent transactions appearing first.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Capital\FinancingTransaction>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/capital/financing_transactions', $params, $opts);
    }

    /**
     * Retrieves a financing transaction for a financing offer.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Capital\FinancingTransaction
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/capital/financing_transactions/%s', $id), $params, $opts);
    }
}
