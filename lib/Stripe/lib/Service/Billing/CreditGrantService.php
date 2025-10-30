<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Billing;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class CreditGrantService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieve a list of credit grants.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Billing\CreditGrant>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/billing/credit_grants', $params, $opts);
    }

    /**
     * Creates a credit grant.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\CreditGrant
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/billing/credit_grants', $params, $opts);
    }

    /**
     * Expires a credit grant.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\CreditGrant
     */
    public function expire($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/billing/credit_grants/%s/expire', $id), $params, $opts);
    }

    /**
     * Retrieves a credit grant.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\CreditGrant
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/billing/credit_grants/%s', $id), $params, $opts);
    }

    /**
     * Updates a credit grant.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\CreditGrant
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/billing/credit_grants/%s', $id), $params, $opts);
    }

    /**
     * Voids a credit grant.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\CreditGrant
     */
    public function voidGrant($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/billing/credit_grants/%s/void', $id), $params, $opts);
    }
}
