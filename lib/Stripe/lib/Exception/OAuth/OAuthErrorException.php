<?php

namespace SimplePay\Vendor\Stripe\Exception\OAuth;

/**
 * Implements properties and methods common to all (non-SPL) SimplePay\Vendor\Stripe OAuth
 * exceptions.
 */
abstract class OAuthErrorException extends \SimplePay\Vendor\Stripe\Exception\ApiErrorException
{
    protected function constructErrorObject()
    {
        if (null === $this->jsonBody) {
            return null;
        }

        return \SimplePay\Vendor\Stripe\OAuthErrorObject::constructFrom($this->jsonBody);
    }
}
