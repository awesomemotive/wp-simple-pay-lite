=== Stripe Payments for WordPress - WP Simple Pay ===
Contributors: moonstonemedia, pderksen, nickyoung87, nekojira
Tags: stripe, payments, credit card, stripe payments, stripe checkout
Requires at least: 4.3
Tested up to: 4.7
Stable tag: 1.6.0
License: GPLv2 or later

Add high conversion Stripe payment forms to your WordPress site in minutes.

== Description ==

In a few simple steps you can start accepting credit card payments with Stripe Checkout on your WordPress site. Stripe continually tests and optimizes their checkout forms to maximize customer conversion.

**What is Stripe Checkout?**

*"Stripe Checkout is an embeddable payment form for desktop, tablet, and mobile devices. It works within your site—customers can pay instantly, without being redirected away to complete the transaction."*

If accepting credit card payments quickly and painlessly with little setup time is what you're looking for, this plugin is for you.

WP Simple Pay is not designed to integrate with more complex shopping cart, form builder or membership site plugins. Many of those plugins already have their own Stripe integrations.

This is a standalone Stripe checkout plugin.

That's it. **No other plugins required.**

>**[Check out our demos & PRO version](https://wpsimplepay.com/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)**

SSL note: Stripe requires that any page hosting a live checkout form be SSL (they should start with `https://`). [See system requirements.](https://wpsimplepay.com/docs/getting-started/system-requirements/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)

= LITE VERSION FEATURES =

* Unlimited payment forms
* Mobile responsive Stripe Checkout overlay
* Display brand or product image in overlay
* Optionally collect customer billing address
* Optionally verify zip/postal code without address
* Support for 12 languages, 25 countries and 139 currencies
* Translation ready
* Bitcoin and Alipay payment options
* Multiple Stripe API key support
* [AffiliateWP](https://affiliatewp.com/) integration
* Specify payment success & failure pages
* Live/Test mode toggle
* Filters, hooks and [code snippets](https://github.com/moonstonemedia/WP-Simple-Pay-Snippet-Library) for developers

= PRO VERSION FEATURES =

* *Everything in Lite plus...*
* Custom fields to capture additional data
* Custom amounts - let customers enter amount to pay
* Coupon code support
* Stripe Subscription support
* Subscription installment plans
* Subscription setup fees
* Subscription trial periods
* [Easy Pricing Tables](https://fatcatapps.com/easypricingtables/) integration
* Optionally collect customer shipping address
* Priority email support with a 24-hour response time during business days

>**[Get More with WP Simple Pay PRO for Stripe](https://wpsimplepay.com/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)**

== Installation ==

[Plugin installation instructions](https://wpsimplepay.com/docs/getting-started/installing-stripe-checkout-lite/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=installation)

== Frequently Asked Questions ==

= Where's your plugin documentation? =

Find our docs at [wpsimplepay.com/docs](https://wpsimplepay.com/docs/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=faq)

= What are the system requirements? =

SSL is required on live checkout pages, and we recommend staying current with both PHP and WP for security reasons and PCI-DSS compliance. [See system requirements.](https://wpsimplepay.com/docs/getting-started/system-requirements/?utm_source=wordpress.org&utm_medium=link&utm_campaign=simple-pay-lite-readme&utm_content=description)

= Can I get notified by email of new releases? =

[Subscribe here](https://www.getdrip.com/forms/6606935/submissions/new) to be notified by email of major features or updates.

= How do I contribute to WP Simple Pay Lite? =

We'd love your help! Here's a few things you can do:

* [Rate our plugin](https://wordpress.org/support/view/plugin-reviews/stripe?postform#postform) and help spread the word!
* Help answer questions in our [community support forum](https://wordpress.org/support/plugin/stripe).
* Report bugs (with steps to reproduce) or submit pull requests [on GitHub](https://github.com/moonstonemedia/WP-Simple-Pay-Lite-for-Stripe).
* Help add or update a [plugin translation](https://translate.wordpress.org/projects/wp-plugins/stripe).

== Screenshots ==

1. Desktop checkout overlay
2. Mobile checkout overlay
3. Settings: Stripe keys
4. Settings: Site-wide defaults

== Changelog ==

= 1.6.0 - March 29, 2017 =

* Dev: System report: Fix for MySQL connection error output.
* Dev: System report: Add SSL check.
* Dev: Updated to Stripe PHP library v4.5.1.

= 1.5.9 - January 5, 2017 =

* Dev: Added action hook taking place after a charge but before the page redirect. Props [@ancentz](https://github.com/ancentz)

= 1.5.8 - December 8, 2016 =

* Dev: System report tweaks for PHP 7 compatibility.
* Dev: Updated to Stripe PHP library v4.3.0.

= 1.5.7 - November 17, 2016 =

* Dev: Fix JavaScript type casting console warnings originating from Stripe.
* Dev: Tested up to WordPress 4.7.
* Dev: Updated to Stripe PHP library v4.1.1.

= 1.5.6 - October 27, 2016 =

* Fix: Additional check for Pro version active.

= 1.5.5 - October 21, 2016 =

* Fix: Corrected test mode not getting set correctly when using new alternate Stripe API key filters.

= 1.5.4 - October 17, 2016 =

* Tweak: Removed alternate Stripe API key support using shortcode attributes due to reliability issues. They must now be added through filters.
* Dev: Now sending application info with each Stripe API call to assist Stripe in debugging issues with accounts using the plugin.
* Dev: Updated to Stripe PHP library v4.0.0.

= 1.5.3 - September 14, 2016 =

* Fix: URL encode store name properly so it displays on payment details screen correctly.
* Dev: Add filter for payment form CSS classes.
* Dev: Move before payment button filter to proper place.
* Dev: Updated to Stripe PHP library v3.22.0.

= 1.5.2 - July 29, 2016 =

* Fix: Extra TLS compatibilty check via updated Stripe PHP library (v3.19.0).
* Feature: Added Stripe TLS requirement to system report.
* Dev: System report tweaks for PHP 7 compatibility.
* Dev: Tested up to WordPress 4.6.

= 1.5.1 - June 18, 2016 =

* Fix: Check for Pro version of WP Simple Pay to prevent fatal error in some cases.
* Dev: Improve store name url encoding and escaping for display on payment success page.
* Dev: Improve product name escaping for display on payment success page.

= 1.5.0 - March 6, 2016 =

* Tweak: Admin toggle switch UI for Test/Live modes updated.
* Tweak: Translations moved from .po/.mo files to official wordpress.org translation packs.
* Tweak: Always enqueue scripts option removed. Now forced on unless dequeued in code.
* Tweak: Stop execution of plugin instead of deactivating if Pro version detected.
* Dev: Harden security by escaping text variables in inline JavaScript with esc_js().
* Dev: Now using Composer to handle PHP library dependencies (i.e. Stripe PHP).
* Dev: Tested up to WordPress 4.5.

= 1.4.6 - November 19, 2015 =

* Dev: Tested up to WordPress 4.4.

= 1.4.5 - September 29, 2015 =

* Fix: Re-added missing files to wordpress.org repository.

= 1.4.4 - September 29, 2015 =

* Fix: Fixed undefined variable PHP error.
* Dev: Updated to most recent Stripe PHP library (v3.4.0).

= 1.4.3 - August 31, 2015 =

* Tweak: Updated locale setting to allow specific languages recently added by Stripe. Also now defaults to "auto" (user's browser configuration).
* Feature: Added System Report page to assist with troubleshooting and support.

= 1.4.2 - August 14, 2015 =

* Fix: Added admin message when trying to activate Pro with Lite still active.
* Tweak: Adjusted name of admin menu label to be shorter.
* Dev: Updated existing sc_redirect filter.
* Dev: Added new sc_redirect_args filter.

= 1.4.1 - August 9, 2015 =

* Tweak: Added deactivation code so Lite and Pro versions don't run at the same time.
* Fix: Prevent conflicts with other plugins using Stripe.
* Dev: Now using Grunt to automate build and file minification tasks.
* Dev: Now JS & CSS files referenced are the minified versions. If SCRIPT_DEBUG set to true, all JS & CSS files referenced are the debug/unminified versions.
* Dev: No longer using the WPupdatePHP library.

= 1.4.0.2 - July 29, 2015 =

* Fixed a bug that was causing a PHP fatal error in some cases.

= 1.4.0.1 - July 27, 2015 =

* Temporary revert back to old domain name (wpstripe.net) due to DNS issues.

= 1.4.0 - July 25, 2015 =

* Added shortcode attributes to allow alternate Stripe API keys other than those stored in the default settings.
* Change of product name (WP Simple Pay Lite for Stripe).
* Major code refactor.
* Updated to most recent Stripe PHP library (v2.3.0).
* Tested up to WordPress 4.3.

= 1.3.3 - May 20, 2015 =

* Added the ability to accept Alipay payments via shortcode (alipay="true" or "auto").
* Added optional Alipay shortcode attributes (alipay_reusable="true" and/or locale="true").
* Added the ability to accept Alipay payments via default settings.
* Added the ability to show payment details below post content via shortcode (payment_details_placement="below").
* Upon payment failure, the human-readable payment failure message is displayed instead of the failure code.
* Updated to most recent Stripe PHP library (v2.1.4).

= 1.3.2 - April 24, 2015 =

* Fixed bug where the data-sc-id attribute of each form was not incrementing when also using custom form IDs.

= 1.3.1 - April 22, 2015 =

* Updated calls to add_query_arg to prevent any possible XSS attacks.
* Added the ability to accept Bitcoin payments via default settings.
* Now checks that host is running PHP 5.3.3 or higher using the WPupdatePHP library.
* Option to always enqueue scripts & styles now enabled by default.
* Updated to most recent Stripe PHP library (v2.1.2).
* Tested up to WordPress 4.2.

= 1.3.0.1 - March 13, 2015 =

* Corrected the Stripe PHP class check to include new v2.0.0+ namepace. Should fix issues when running other Stripe-related plugins that utilize a version of the Stripe PHP library less than v2.0.0.

= 1.3.0 - March 12, 2015 =

* Added the ability to accept Bitcoin payments via shortcode (bitcoin="true").
* Updated to most recent Stripe PHP library (v2.1.1), which now requires PHP 5.3.3 or higher.
* Scripts and styles now only enqueued on posts and pages where required.
* Added option to always enqueue scripts and styles on every post and page.
* Added function to remove unwanted formatting in shortcodes.
* Cleaned up payment success and error details HTML.
* Fixed duplicate payment success and failure output for rare themes that render multiple post content areas.
* Added id attribute to shortcode to allow custom form id's.
* Now sanitizes Stripe API keys with invalid copied characters following a space.

= 1.2.9 =

* Test/Live mode toggle switch updated. Now CSS only.

= 1.2.8 =

* Updated to most recent Stripe PHP library (v1.17.3).
* Updated to most recent Bootstrap Switch library (v3.2.2).
* Tested up to WordPress 4.1.

= 1.2.7 =

* Removed shipping address support. Not supported natively by Stripe Checkout now ([see docs](https://stripe.com/docs/checkout)).
* Simplified text domain function.

= 1.2.6 =

* Added option to disable the default success message output.

= 1.2.5.1 =

* Added missing PHP file.

= 1.2.5 =

* Fixed a bug with the remember me option.
* Fixed a bug with the test_mode attribute.

= 1.2.4 =

* Allow display of more charge details on the payment success page. This is made possible by utilizing the Stripe charge ID to retrieve the entire charge object via the Stripe API.
* Updated to most recent Stripe PHP library (v1.17.2).
* Updated 3rd party JS/CSS library Bootstrap Switch.
* Improved messaging for minimum required amount by Stripe (50 units).

= 1.2.3 =

* Added option to save settings when uninstalling.
* Added test_mode attribute to specify test mode per form.

= 1.2.2 =

* Removed unnecessary code previously required for add-ons.
* Updated to most recent Stripe PHP library (v1.17.1).

= 1.2.1 =

* Fixed a bug with the disable CSS option.
* Fixed a warning appearing for Network Admins of a multisite installation.

= 1.2.0 =

* Added verify_zip shortcode attribute.
* Added failure_redirect_url shortcode attribute.
* Updated sc_redirect filter to allow modification for failed redirect URLs.
* Fixed compatibility issue with the [WordPress SEO plugin](https://wordpress.org/plugins/wordpress-seo/).
* Tested up to WordPress 4.0.

= 1.1.2 =

* Fixed bug where a blank email address was getting sent and causing some payments to hang or fail.
* Now using [WP Session Manager](https://github.com/ericmann/wp-session-manager) to handle session data.

= 1.1.1 =

* Added prefill_email shortcode attribute.
* Shipping name and address are now stored in payment metadata if enabled. Stripe does not store them natively.
* Added basic form CSS and the option to disable it.
* Added many filters and hooks for extensibility.
* Implemented add-on infrastructure.
* General usuability improvements to settings pages.
* Updated to most recent Stripe PHP library (v1.16.0).
* Included most recent Parsley JS validation library.
* Moved in-plugin help to online help at wpstripe.net.

= 1.1.0 =

* Added image_url shortcode attribute.
* Added currency shortcode attribute.
* Added checkout_button_label shortcode attribute.
* Added payment_button_label shortcode attribute (thanks @enollo).
* Added billing shortcode attribute.
* Added shipping shortcode attribute.
* Added enable_remember shortcode attribute.
* Added success_redirect_url shortcode attribute.
* Updated to most recent Stripe PHP library (v1.13.0).
* Added a couple of action and filter hooks.
* Fixed bug where other instances of Stripe class were causing errors.
* Removed a default string being added to customer description in Stripe dashboard.

= 1.0.1 =

* Fixed bug where customers would not receive an email receipt after purchase.

= 1.0.0 =

* Initial release.
