<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Treasury;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class OutboundTransferService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Returns a list of OutboundTransfers sent from the specified FinancialAccount.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Treasury\OutboundTransfer>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/treasury/outbound_transfers', $params, $opts);
    }

    /**
     * An OutboundTransfer can be canceled if the funds have not yet been paid out.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function cancel($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/treasury/outbound_transfers/%s/cancel', $id), $params, $opts);
    }

    /**
     * Creates an OutboundTransfer.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/treasury/outbound_transfers', $params, $opts);
    }

    /**
     * Retrieves the details of an existing OutboundTransfer by passing the unique
     * OutboundTransfer ID from either the OutboundTransfer creation request or
     * OutboundTransfer list.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/treasury/outbound_transfers/%s', $id), $params, $opts);
    }
}
