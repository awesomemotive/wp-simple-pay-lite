<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Service;

class AccountLinkService extends \SimplePay\Vendor\Stripe\Service\AbstractService
{
    /**
     * Creates an AccountLink object that includes a single-use SimplePay\Vendor\Stripe URL that the
     * platform can redirect their user to in order to take them through the Connect
     * Onboarding flow.
     *
     * @param null|array $params
     * @param null|array|\SimplePay\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\AccountLink
     */
    public function create($params = null, $opts = null)
    {
        return $this->request('post', '/v1/account_links', $params, $opts);
    }
}
