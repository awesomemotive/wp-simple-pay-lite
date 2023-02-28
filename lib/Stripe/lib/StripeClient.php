<?php

// File generated from our OpenAPI spec

namespace SimplePay\Vendor\Stripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \SimplePay\Vendor\Stripe\Service\AccountLinkService $accountLinks
 * @property \SimplePay\Vendor\Stripe\Service\AccountSessionService $accountSessions
 * @property \SimplePay\Vendor\Stripe\Service\AccountService $accounts
 * @property \SimplePay\Vendor\Stripe\Service\ApplePayDomainService $applePayDomains
 * @property \SimplePay\Vendor\Stripe\Service\ApplicationFeeService $applicationFees
 * @property \SimplePay\Vendor\Stripe\Service\Apps\AppsServiceFactory $apps
 * @property \SimplePay\Vendor\Stripe\Service\BalanceService $balance
 * @property \SimplePay\Vendor\Stripe\Service\BalanceTransactionService $balanceTransactions
 * @property \SimplePay\Vendor\Stripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \SimplePay\Vendor\Stripe\Service\Capital\CapitalServiceFactory $capital
 * @property \SimplePay\Vendor\Stripe\Service\ChargeService $charges
 * @property \SimplePay\Vendor\Stripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \SimplePay\Vendor\Stripe\Service\CountrySpecService $countrySpecs
 * @property \SimplePay\Vendor\Stripe\Service\CouponService $coupons
 * @property \SimplePay\Vendor\Stripe\Service\CreditNoteService $creditNotes
 * @property \SimplePay\Vendor\Stripe\Service\CustomerService $customers
 * @property \SimplePay\Vendor\Stripe\Service\DisputeService $disputes
 * @property \SimplePay\Vendor\Stripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \SimplePay\Vendor\Stripe\Service\EventService $events
 * @property \SimplePay\Vendor\Stripe\Service\ExchangeRateService $exchangeRates
 * @property \SimplePay\Vendor\Stripe\Service\FileLinkService $fileLinks
 * @property \SimplePay\Vendor\Stripe\Service\FileService $files
 * @property \SimplePay\Vendor\Stripe\Service\FinancialConnections\FinancialConnectionsServiceFactory $financialConnections
 * @property \SimplePay\Vendor\Stripe\Service\GiftCards\GiftCardsServiceFactory $giftCards
 * @property \SimplePay\Vendor\Stripe\Service\Identity\IdentityServiceFactory $identity
 * @property \SimplePay\Vendor\Stripe\Service\InvoiceItemService $invoiceItems
 * @property \SimplePay\Vendor\Stripe\Service\InvoiceService $invoices
 * @property \SimplePay\Vendor\Stripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \SimplePay\Vendor\Stripe\Service\MandateService $mandates
 * @property \SimplePay\Vendor\Stripe\Service\OAuthService $oauth
 * @property \SimplePay\Vendor\Stripe\Service\OrderService $orders
 * @property \SimplePay\Vendor\Stripe\Service\PaymentIntentService $paymentIntents
 * @property \SimplePay\Vendor\Stripe\Service\PaymentLinkService $paymentLinks
 * @property \SimplePay\Vendor\Stripe\Service\PaymentMethodService $paymentMethods
 * @property \SimplePay\Vendor\Stripe\Service\PayoutService $payouts
 * @property \SimplePay\Vendor\Stripe\Service\PlanService $plans
 * @property \SimplePay\Vendor\Stripe\Service\PriceService $prices
 * @property \SimplePay\Vendor\Stripe\Service\ProductService $products
 * @property \SimplePay\Vendor\Stripe\Service\PromotionCodeService $promotionCodes
 * @property \SimplePay\Vendor\Stripe\Service\QuotePhaseService $quotePhases
 * @property \SimplePay\Vendor\Stripe\Service\QuoteService $quotes
 * @property \SimplePay\Vendor\Stripe\Service\Radar\RadarServiceFactory $radar
 * @property \SimplePay\Vendor\Stripe\Service\RefundService $refunds
 * @property \SimplePay\Vendor\Stripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \SimplePay\Vendor\Stripe\Service\ReviewService $reviews
 * @property \SimplePay\Vendor\Stripe\Service\SetupAttemptService $setupAttempts
 * @property \SimplePay\Vendor\Stripe\Service\SetupIntentService $setupIntents
 * @property \SimplePay\Vendor\Stripe\Service\ShippingRateService $shippingRates
 * @property \SimplePay\Vendor\Stripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \SimplePay\Vendor\Stripe\Service\SourceService $sources
 * @property \SimplePay\Vendor\Stripe\Service\SubscriptionItemService $subscriptionItems
 * @property \SimplePay\Vendor\Stripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \SimplePay\Vendor\Stripe\Service\SubscriptionService $subscriptions
 * @property \SimplePay\Vendor\Stripe\Service\Tax\TaxServiceFactory $tax
 * @property \SimplePay\Vendor\Stripe\Service\TaxCodeService $taxCodes
 * @property \SimplePay\Vendor\Stripe\Service\TaxRateService $taxRates
 * @property \SimplePay\Vendor\Stripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \SimplePay\Vendor\Stripe\Service\TestHelpers\TestHelpersServiceFactory $testHelpers
 * @property \SimplePay\Vendor\Stripe\Service\TokenService $tokens
 * @property \SimplePay\Vendor\Stripe\Service\TopupService $topups
 * @property \SimplePay\Vendor\Stripe\Service\TransferService $transfers
 * @property \SimplePay\Vendor\Stripe\Service\Treasury\TreasuryServiceFactory $treasury
 * @property \SimplePay\Vendor\Stripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \SimplePay\Vendor\Stripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \SimplePay\Vendor\Stripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}
