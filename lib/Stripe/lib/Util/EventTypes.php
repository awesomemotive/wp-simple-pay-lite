<?php

namespace SimplePay\Vendor\Stripe\Util;

class EventTypes
{
    const thinEventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \SimplePay\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \SimplePay\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \SimplePay\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \SimplePay\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
        // The end of the section generated from our OpenAPI spec
    ];
}
