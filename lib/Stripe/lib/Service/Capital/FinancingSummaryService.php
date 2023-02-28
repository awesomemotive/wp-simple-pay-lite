<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Capital;

class FinancingSummaryService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieve the financing state for the account that was authenticated in the
     * request.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Capital\FinancingSummary
     */
    public function retrieve($params = null, $opts = null)
    {
        return $this->request('get', '/v1/capital/financing_summary', $params, $opts);
    }
}
