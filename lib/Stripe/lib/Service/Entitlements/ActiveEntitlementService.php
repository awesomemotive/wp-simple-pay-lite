<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Entitlements;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class ActiveEntitlementService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieve a list of active entitlements for a customer.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Entitlements\ActiveEntitlement>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/entitlements/active_entitlements', $params, $opts);
    }

    /**
     * Retrieve an active entitlement.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Entitlements\ActiveEntitlement
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/entitlements/active_entitlements/%s', $id), $params, $opts);
    }
}
