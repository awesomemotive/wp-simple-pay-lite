<?php

namespace SimplePay\Vendor\Stripe\ApiOperations;

/**
 * Trait for listable resources. Adds a `all()` static method to the class.
 *
 * This trait should only be applied to classes that derive from StripeObject.
 */
trait All
{
    /**
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Collection of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('get', $url, $params, $opts);
        $obj = \SimplePay\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        if (!($obj instanceof \SimplePay\Vendor\Stripe\Collection)) {
            throw new \SimplePay\Vendor\Stripe\Exception\UnexpectedValueException(
                'Expected type ' . \SimplePay\Vendor\Stripe\Collection::class . ', got "' . \get_class($obj) . '" instead.'
            );
        }
        $obj->setLastResponse($response);
        $obj->setFilters($params);

        return $obj;
    }
}
