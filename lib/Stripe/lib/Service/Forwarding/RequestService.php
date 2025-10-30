<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service\Forwarding;

/**
 * @phpstan-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \SimplePay\Vendor\Stripe\Util\RequestOptions
 */
class RequestService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Lists all ForwardingRequest objects.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection<\SimplePay\Vendor\Stripe\Forwarding\Request>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/forwarding/requests', $params, $opts);
    }

    /**
     * Creates a ForwardingRequest object.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Forwarding\Request
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/forwarding/requests', $params, $opts);
    }

    /**
     * Retrieves a ForwardingRequest object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Forwarding\Request
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/forwarding/requests/%s', $id), $params, $opts);
    }
}
