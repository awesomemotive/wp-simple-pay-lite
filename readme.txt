=== Stripe Payments for WordPress - WP Simple Pay ===
Contributors: pderksen, spencerfinnell, adamjlea, mordauk, cklosows, sdavis2702, dgoldak, nickyoung87, nekojira
Tags: stripe, payments, credit card, stripe payments, stripe checkout
Requires at least: 4.9
Tested up to: 5.3.0
Stable tag: 2.3.2
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

= Can I get notified by email of new releases? =

[Subscribe here](https://wpsimplepay.com/subscribe/) to be notified by email of major features or updates.

= How do I contribute to WP Simple Pay? =

We'd love your help! Here are a few things you can do:

* [Rate our plugin](https://wordpress.org/support/plugin/stripe/reviews/#new-post) and help spread the word!
* Help answer questions in our [community support forum](https://wordpress.org/support/plugin/stripe).
* Report bugs (with steps to reproduce) or submit pull requests [on GitHub](https://github.com/wpsimplepay/WP-Simple-Pay-Lite-for-Stripe).
* Help add or update a [plugin translation](https://translate.wordpress.org/projects/wp-plugins/stripe).

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

= 2.1.1 - March 20, 2019 =

* Fix: Ensure values over 999 are properly processed in Stripe Checkout.

= 2.1.0 - March 15, 2019 =

* New: You can now easily connect your Stripe account with Stripe Connect. See your settings page for more details.
* New: Enable ZIP/Postal Code verification by default on new forms.
* New: Allow "Company Name" value to be blank.
* New: Add "Country" setting in Stripe Setup settings to send with Stripe API requests.
* Fix: Delete extraneous options on uninstall routine.
* Fix: Prefix Stripe API request from library with "WordPress".
* Dev: Updated to Stripe PHP library v6.30.4.
* Dev: Updated to use Stripe API version 2019-02-19.
* Dev: Remove unused files.

= 2.0.12 - November 1, 2018 =

* Dev: Added filter hook to add or modify arguments when a Stripe customer is created.
* Dev: Added filter hook to allow additional attributes to be added to the payment form tag.
* Dev: Updated to Stripe PHP library v6.20.0.

= 2.0.11 - July 27, 2018 =

* Tweak: Various updates from Pro version.
* Dev: Updated Accounting JS & Chosen JS libraries.
* Dev: System report: Update Stripe API endpoint for the TLS 1.2 compatibility check.
* Dev: Updated to Stripe PHP library v6.11.0.

= 2.0.10 - May 21, 2018 =

* Tweak: PHP 5.4 or higher now required.
* Dev: Updated to Stripe PHP library v6.7.1.

= 2.0.9 - April 2, 2018 =

* Fix: Fix and simplify payment form previews.
* Fix: Detection and warning about upcoming PHP 5.4 requirement.
* Fix: Dequeue legacy public CSS in addition to main public CSS when "Default Plugin Styles" option is disabled.
* Fix: Error when activating plugin with WP-CLI.
* Tweak: Removed Bitcoin support inline with Stripe (https://stripe.com/blog/ending-bitcoin-support).
* Dev: System report: Add mbstring (Multibyte String) check.
* Dev: Updated to Stripe PHP library v5.9.2.

= 2.0.8 - January 5, 2018 =

* Fix: Add option of switching to native PHP sessions.
* Fix: Force use of native PHP sessions when hosting with Pantheon.

= 2.0.7 - December 21, 2017 =

* Fix: (Better) session handling to work across various hosts. Back to using the current version of WP Session Manager (https://github.com/ericmann/wp-session-manager) (2.0.2).
* Fix: Force use of native PHP sessions when hosting with Pantheon and using their native PHP sessions plugin.
* Dev: Updated to Stripe PHP library v5.8.0.
* Dev: Updated jQuery Validation & Chosen JS libraries.

= 2.0.6 - December 12, 2017 =

* Fix: Check for an existing session before starting a new one.

= 2.0.5 - December 12, 2017 =

* Fix: Session handling updated to work across various managed hosts. Now uses code from WP Native PHP Sessions (https://github.com/pantheon-systems/wp-native-php-sessions) over previously used WP Session Manager (https://github.com/ericmann/wp-session-manager).
* Fix: PHP 7.2 incompatibility - Misuse of reserved keyword "Object".
* Fix: Payment receipt session error message produced by a shortcode was improperly appearing in WP admin.
* Dev: Added action hook for adding metabox setting panel content.
* Dev: Updated to Stripe PHP library v5.7.0.

= 2.0.4 - October 25, 2017 =

* Feature: Added option to set the payment success page (or redirect URL) per payment form.
* Dev: Various structure related improvements.
* Dev: Better handling of alternate Stripe API keys.
* Dev: Updated to Stripe PHP library v5.4.0.

= 2.0.3 - September 10, 2017 =

* Fix: Change directory path for legacy Stripe PHP library inclusion.
* Fix: Undefined PHP constant that could sometimes cause a PHP warning.

= 2.0.2 - September 8, 2017 =

* Fix: Prevent activation when WP Simple Pay Pro is active to avoid a fatal error.

= 2.0.1 - September 5, 2017 =

* Fix: Change plugin main file name so it doesn't deactivate on upgrade.

= 2.0.0 - September 5, 2017 =

* Feature: Payment form settings overhauled to match WP Simple Pay Pro v3 update.
* Feature: Payment forms can be optionally saved as drafts and previewed.
* Feature: Button added to post editor for quickly adding payment forms to pages.
* Feature: Shortcodes for payment forms have been notably simplified.
* Feature: Shipping information can now be captured in the Stripe Checkout overlay.
* Feature: Added currency formatting options to settings.
* Feature: Payment details can now be edited using the standard post editor.
* Feature: Admin bar now indicates if in test mode.
* Fix: Removed support for Alipay since it is no longer supported through Stripe Checkout.
* Dev: Removed POT file since WordPress has better ways of handling translations now.
* Dev: Now using custom post type to hold individual form settings and to match Pro v3 update.
* Dev: Add official PHP version requirement check from wordpress.org to readme.txt header.
* Dev: Tested up to WordPress 4.8.
* Dev: Updated to Stripe PHP library v5.2.0.

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

== Upgrade Notice ==
