=== WP Simple Pay Lite for Stripe ===
Contributors: pderksen, nickyoung87
Tags: stripe, stripe checkout, simple stripe checkout, ecommerce, e-commerce
Requires at least: 3.9
Tested up to: 4.3
Stable tag: 1.4.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest way to add high conversion Stripe checkout forms to your site and start accepting payments.

== Description ==

Add highly optimized Stripe checkout form overlays to your site in a few simple steps.

Instead of spending time building your own checkout forms you can use Stripe's, which are continually tested for high conversion.

> "Stripe Checkout is an embeddable payment form for desktop, tablet, and mobile devices. It works within your site—customers can pay instantly, without being redirected away to complete the transaction."

[View WP Simple Pay for Stripe Live Demos](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

This is a simple standalone Stripe checkout plugin. That's it. **No other plugins required.**

**SSL Note:** Stripe does require that pages hosting the checkout form be SSL (they should start with `https://`). [Read more about SSL](https://stripe.com/help/ssl)

###Start Accepting Payments in 3 Easy Steps###

It only takes a couple minutes to add a Stripe payment form to your site.

1. Activate the plugin, go to Settings > WP Simple Pay Lite for Stripe, then enter your Stripe API keys.
1. Edit the post or page where you want the payment button and checkout form to appear.
1. Add a simple shortcode. [See shortcode reference](http://wpstripe.net/docs/shortcodes/stripe-checkout/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

Viola! Now a payment button that opens your Stripe checkout form in an overlay will pop up.

###Available in WP Simple Pay Pro for Stripe Only###

* **User Entered Amounts** - Allow customers enter an amount they want to pay.
* **Coupon Codes** - Setup discount codes in your Stripe dashboard for customers to apply to their total.
* **Custom Fields** - Record additional non-standard data along with each Stripe payment.
* **Subscriptions** - Let customers pay and sign up for your Stripe recurring plans.

[See Pricing & Demos for Pro](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

Here are a few shortcode examples (amounts in U.S. cents):

`[stripe name="The Awesome Store" amount="1999" description="The Awesome Blueprint Book"]`

`[stripe name="The Awesome Store" amount="1999" description="Five Awesome Coaching Calls"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" image_url="http://www.example.com/book_image.jpg"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" checkout_button_label="Now only {{amount}}!" enable_remember="false"]`

[Documentation & Getting Started](http://wpstripe.net/docs/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

[Shortcode Reference](http://wpstripe.net/docs/shortcodes/stripe-checkout/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

Easily toggle between test and live mode until you're ready.

If you want your customers to receive standard email receipts, make sure you enable this setting in your Stripe dashboard.

[Learn more about WP Simple Pay Pro for Stripe](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

###Updates###

* [Get notified when new features are released](http://eepurl.com/YMXvP)
* [Follow this project on Github](https://github.com/pderksen/WP-Stripe-Checkout)

== Installation ==

There are three ways to install this plugin.

= 1. Admin Search =
1. In your Admin, go to menu Plugins > Add.
1. Search for `WP Simple Pay`.
1. Find the plugin that's labeled `WP Simple Pay Lite for Stripe`.
1. Look for the author name `Phil Derksen` on the plugin.
1. Click to install.
1. Activate the plugin.
1. A new menu item `WP Simple Pay Lite for Stripe` will appear in the main menu.

= 2. Download & Upload =
1. Download the plugin (a zip file) on the right column of this page.
1. In your Admin, go to menu Plugins > Add.
1. Select the tab "Upload".
1. Upload the .zip file you just downloaded.
1. Activate the plugin.
1. A new menu item `WP Simple Pay Lite for Stripe` will appear in the main menu.

= 3. FTP Upload =
1. Download the plugin (.zip file) on the right column of this page.
1. Unzip the zip file contents.
1. Upload the `stripe` folder to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. A new menu item `WP Simple Pay Lite for Stripe` will appear in the main menu.

== Frequently Asked Questions ==

[Plugin Documentation](http://wpstripe.net/docs/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

== Screenshots ==

1. Desktop checkout overlay
2. Mobile checkout overlay
3. Settings: Stripe keys
4. Settings: Site-wide defaults

== Changelog ==

= 1.4.0.1 = July 27, 2015 =

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
