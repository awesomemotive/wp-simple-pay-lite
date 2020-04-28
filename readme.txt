=== Stripe Payments for WordPress - WP Simple Pay ===
Contributors: pderksen, spencerfinnell, adamjlea, mordauk, cklosows, sdavis2702, dgoldak, nickyoung87, nekojira
Tags: stripe, payments, credit card, stripe payments, stripe checkout
Requires at least: 4.9
Tested up to: 5.3.2
Stable tag: 2.3.3
Requires PHP: 5.6
License: GPLv2 or later

Add high conversion Stripe payment forms to your WordPress site in minutes.

== Description ==

In a few simple steps you can start accepting credit card payments with Stripe Checkout on your WordPress site.

**What is Stripe Checkout?**

*"Stripe Checkout is a drop-in payment flow for desktop, tablet, and mobile devices. Rely on a checkout page that’s continuously tested and updated to offer a frictionless payments experience. PCI DSS and SCA—ready without any changes to your website."*

If accepting credit card payments quickly and painlessly is what you're looking for, this plugin is for you.

[Stripe Checkout](https://stripe.com/payments/checkout) is continually optimized across millions of transactions to maximize customer conversions.

WP Simple Pay is a standalone Stripe Checkout plugin built by [Sandhills Development](http://sandhillsdev.com). No complex shopping cart, form builder or membership site plugin needed.

That's it. *No other plugins required.*

>**[Check out our demos & Pro version](https://demo.wpsimplepay.com/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)**

= LITE VERSION FEATURES =

* Unlimited payment forms
* Mobile-optimized Stripe Checkout pages
* Display brand or product image on Stripe Checkout pages
* Optionally collect customer billing & shipping addresses
* Optionally verify zip/postal code without address
* Support for 14 languages, 30+ countries and 135+ currencies
* Stripe Connect support for easier setup
* PCI DSS and Strong Customer Authentication (SCA) support for improved security
* Translation ready
* [AffiliateWP](https://affiliatewp.com/) integration
* Specify payment success & failure pages
* Live/Test mode toggle
* [Code snippets](https://github.com/wpsimplepay/WP-Simple-Pay-Snippet-Library/) & [hook reference](https://docs.wpsimplepay.com/articles/action-filter-hooks/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description) for developers

= PRO VERSION FEATURES =

* *Everything in Lite plus...*
* Drag & drop form design controls
* Unlimited custom fields to capture additional data
* Custom amounts - let customers enter an amount to pay
* Coupon code support
* On-site checkout (no redirect) with custom forms
* Embedded & overlay form display options
* Apple Pay & Google Pay support with custom forms
* reCAPTCHA v3 invisible verification support
* Stripe Subscription support
* Subscription installment plans
* Subscription setup fees
* Subscription trial periods
* [Easy Pricing Tables](https://fatcatapps.com/easypricingtables/) integration
* Priority email support with a 24-hour response time during business days

>**[Click here to upgrade to WP Simple Pay Pro](https://wpsimplepay.com/lite-vs-pro/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)**

== Installation ==

The easiest way to install WP Simple Pay is to search for it via your site’s Dashboard.

= Step-by-step instructions =

1. Log in to your site’s dashboard (e.g. www.yourdomain.com/wp-admin).
2. Click on the “Plugins” tab in the left panel, then click “Add New”.
3. Search for “Stripe” or “WP Simple Pay” and find our plugin near the top.
4. Install it by clicking the “Install Now” link.
5. When installation finishes, click “Activate Plugin”.
6. A new menu item “Simple Pay Lite” should appear in your dashboard.

If you prefer installing manually you can [download the plugin ZIP file here](https://downloads.wordpress.org/plugin/stripe.latest-stable.zip).

Additional documentation at [docs.wpsimplepay.com](https://docs.wpsimplepay.com/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=installation).

== Frequently Asked Questions ==

= Where's your plugin documentation? =

Find our docs at [docs.wpsimplepay.com](https://docs.wpsimplepay.com/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=faq).

= Where can I learn more about accepting one-time and recurring payments on the web? =

[Subscribe to our newsletter](https://wpsimplepay.com/subscribe/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=faq) and once every 2-3 weeks we’ll email you our latest in-depth article packed with actionable advice for store owners. On occasion we’ll send you exclusive promotions and major release announcements.

= How do I contribute to WP Simple Pay? =

We'd love your help! Here are a few things you can do:

* [Rate our plugin](https://wordpress.org/support/plugin/stripe/reviews/#new-post) and help spread the word!
* Help answer questions in our [community support forum](https://wordpress.org/support/plugin/stripe).
* Report bugs (with steps to reproduce) or submit pull requests [on GitHub](https://github.com/wpsimplepay/wp-simple-pay-lite).
* Add or update a [plugin translation](https://translate.wordpress.org/projects/wp-plugins/stripe).

== Screenshots ==

1. Desktop Stripe Checkout example
2. Mobile Stripe Checkout example
3. Payment forms listing
4. Payment form settings: One-time amount
5. Payment form settings: Payment button
6. Payment form settings: Stripe Checkout display
7. Settings: Payment confirmation details
8. Settings: Connect with Stripe, Test mode
9. Settings: Site-wide defaults

== Changelog ==

= 2.4.0 - April XX, 2020 =

* New: Stripe Checkout - Allow collection of Shipping Address.
* New: Implement confirmation template tag support for accessing payment, subscription, and customer record data via dynamic tags such as `{customer:name}`.
* New: Split structural and visual styles and inherit more theme defaults when no styles are applied.
* New: Disable entire form during submission.
* New: Allow field values to be set dynamically with via `simpay_form_{$form_id}_field_{$field_id}_default_value` filters.
* New: Improve default form styles.
* New: Stripe Checkout - Add support for Stripe Checkout's "Booking", "Donate", and "Pay" button types.
* New: Stripe Checkout - Automatically remove generated Customer, Product, and Plan records upon completed Stripe Checkout Sessions.
* New: Stripe Checkout - Add separate "Payment Cancelled" page setting for incomplete Stripe Checkout Sessions.
* New: Stripe Checkout - Add notice about branding options in form settings.
* Fix: Stripe Checkout - Fall back to generic "WP Simple Pay" line item has no name.
* Fix: Ensure adequate spacing under Payment Form title with opinionated styles.
* Fix: WordPress 5.4 UI compatibility.
* Fix: Reduce complexity of "Upgrade" submenu item for Lite.
* Dev: Use WordPress core custom post type screens for managing Payment Forms.
* Dev: Update Stripe API PHP library to `7.28.0`.
* Dev: Update Stripe API version to `2020-03-02`.
* Dev: Add `\SimplePay\Core\Utils\Collection` for managing generic registries.
* Dev: Use WordPress core `.button` styles for WP Simple Pay button base.

= 2.3.3 - January 7, 2020 =

* Fix: Handle saving payment confirmation messages in WordPress 5.3.1+
* Dev: Introduce `simpay_stripe_api_publishable_key` and `simpay_stripe_api_secret_key` filters.
* Dev: Introduce `simpay_customer_create` filter to return a Customer ID and short circuit creation.
* Dev: Introduce `\SimplePay\Core\Payments\Payment_Confirmation\get_confirmation_data()` for use in custom snippets to return any relevant confirmation data.
* Dev: Add `?form_id=` to Payment Confirmation and Error redirect URLs.

= 2.3.2 - November 13, 2019 =

* Fix: Stripe Checkout - clarify "Require Billing Address" form setting description.
* Fix: Stripe Checkout - Use Site Title if Company Name field is blank.
* Fix: Ensure custom cron schedule is registered.
* Fix: Ensure Statement Descriptor always results in a valid string.
* Fix: Ensure WordPress 5.3 admin UI appears correctly.
* Fix: Avoid PHP notices for undefined Stripe objects on payment confirmation.
* Fix: Avoid rounding error when converting amounts to cents in PHP 7.1+.
* Fix: Do not reference "Stripe Checkout overlay" in setting descriptions.
* Fix: IE 11 Javascript support for `Promise` and `Object.assign`
* Fix: IE 11 CSS support for `flexbox` alignment.
* Fix: Avoid uncaught PHP error while handling legacy `simpay_stripe_charge_args` filter.

= 2.3.1 - September 17, 2019 =

* Fix: Stripe Checkout - only generate a Customer record when collecting Customer-specific data.
* Fix: Stripe Checkout - update available locales (add Polish, Portuguese).

= 2.3.0 - September 12, 2019 =

* New: Strong Customer Authentication (SCA) support.
* New: Support Stripe's off-site Checkout pages.
* New: Improve Stripe connected account information in admin settings.
* New: Help WP Simple Pay improve by reporting usage analytics.
* Fix: Remove extra apostrophe escaping from meta in the Stripe Dashboard.
* Dev: Update to v6.43.0 of Stripe's PHP API library.
* Dev: Remove WP_Session library.
* Dev: Enforce Stripe's PHP library `cURL` requirement.
* Dev: Deprecated many hooks/filters that no longer apply to the new payment flows.
       Please review any custom snippets that may change functionality.

= 2.2.0 - May 13, 2019 =

* New: Save Billing Address data to Stripe Charge record.
* New: Save Shipping Address data to Stripe Customer record.
* New: Improve onboarding notices and alerts.
* New: Run `simpay_{$filter}` alongside all usage of `simpay_form_{$form_id}_{$filter}`
* New: Add helpful hint about additional fields available while using Stripe Checkout.
* New: Add option to disconnect from Stripe.
* New: Show site administrators a notice when Stripe API keys are missing.
* Fix: Avoid Javascript error in Internet Explorer 11.
* Fix: Do not pass `country` to Stripe Checkout configuration.
* Dev: Update company name throughout files.
* Dev: Updated to Stripe PHP library v6.34.2.
* Dev: Updated to use Stripe API version 2019-03-14.

== Upgrade Notice ==
