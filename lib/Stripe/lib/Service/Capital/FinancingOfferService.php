<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Capital;

class FinancingOfferService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieves the financing offers available for Connected accounts that belong to
     * your platform.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Capital\FinancingOffer>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/capital/financing_offers', $params, $opts);
    }

    /**
     * Acknowledges that platform has received and delivered the financing_offer to the
     * intended merchant recipient. This is required to make the application
     * accessible.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Capital\FinancingOffer
     */
    public function markDelivered($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/capital/financing_offers/%s/mark_delivered', $id), $params, $opts);
    }

    /**
     * Get the details of the financing offer.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Capital\FinancingOffer
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/capital/financing_offers/%s', $id), $params, $opts);
    }
}
