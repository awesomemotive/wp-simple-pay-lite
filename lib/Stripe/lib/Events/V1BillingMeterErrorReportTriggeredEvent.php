<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe\Events;

/**
 * @property \SimplePay\Vendor\Stripe\RelatedObject $related_object Object containing the reference to API resource relevant to the event
 * @property \SimplePay\Vendor\Stripe\EventData\V1BillingMeterErrorReportTriggeredEventData $data data associated with the event
 */
class V1BillingMeterErrorReportTriggeredEvent extends \SimplePay\Vendor\Stripe\V2\Event
{
    const LOOKUP_TYPE = 'v1.billing.meter.error_report_triggered';

    /**
     * Retrieves the related object from the API. Make an API request on every call.
     *
     * @throws \SimplePay\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \SimplePay\Vendor\Stripe\Billing\Meter
     */
    public function fetchRelatedObject()
    {
        list($object, $options) = $this->_request(
            'get',
            $this->related_object->url,
            [],
            ['stripe_account' => $this->context],
            [],
            'v2'
        );

        return \SimplePay\Vendor\Stripe\Util\Util::convertToStripeObject($object, $options, 'v2');
    }
}
