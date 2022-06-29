<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Treasury;

class DebitReversalService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of DebitReversals.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Treasury\DebitReversal>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/treasury/debit_reversals', $params, $opts);
    }

    /**
     * Reverses a ReceivedDebit and creates a DebitReversal object.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Treasury\DebitReversal
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/treasury/debit_reversals', $params, $opts);
    }

    /**
     * Retrieves a DebitReversal object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Treasury\DebitReversal
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/treasury/debit_reversals/%s', $id), $params, $opts);
    }
}
