<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\TestHelpers\Issuing;

class AuthorizationService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Capture a test-mode authorization.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Issuing\Authorization
     */
    public function capture($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/issuing/authorizations/%s/capture', $id), $params, $opts);
    }

    /**
     * Create a test-mode authorization.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Issuing\Authorization
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/issuing/authorizations', $params, $opts);
    }

    /**
     * Expire a test-mode Authorization.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Issuing\Authorization
     */
    public function expire($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/issuing/authorizations/%s/expire', $id), $params, $opts);
    }

    /**
     * Increment a test-mode Authorization.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Issuing\Authorization
     */
    public function increment($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/issuing/authorizations/%s/increment', $id), $params, $opts);
    }

    /**
     * Reverse a test-mode Authorization.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Issuing\Authorization
     */
    public function reverse($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/issuing/authorizations/%s/reverse', $id), $params, $opts);
    }
}
