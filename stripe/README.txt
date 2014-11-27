=== Simple Stripe Checkout ===
Contributors: pderksen, nickyoung87
Tags: stripe, stripe checkout, simple stripe checkout, ecommerce, e-commerce
Requires at least: 3.8.5
Tested up to: 4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest way to add a high conversion Stripe Checkout form to your site and start getting paid.

== Description ==

Add a highly optimized Stripe Checkout form overlay to your site in a few simple steps.

Instead of spending time building your own checkout forms you can use Stripe's, which is continually tested for high conversion.

> "Stripe Checkout is an embeddable payment form for desktop, tablet, and mobile devices. It works within your site—customers can pay instantly, without being redirected away to complete the transaction."

[View Stripe Checkout Live Demos](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

###Plugin Requirements###

This is a simple standalone Stripe checkout plugin. That's it. No other plugins required.

Note that Stripe suggests that the pages hosting the checkout form be SSL (they should start with `https://`). [Read more about SSL](https://stripe.com/help/ssl).

###Start Accepting Payments in 3 Easy Steps###

It only takes a couple minutes to add a payment form to your site.

1. Activate the plugin, go to Settings > Simple Stripe Checkout, then enter your Stripe keys.
1. Edit the post or page where you want the payment button and checkout form to appear.
1. Add a simple shortcode.

Viola! Now a payment button that opens your checkout form in an overlay will pop up.

###Available in Stripe Checkout Pro Only###

* **User Entered Amounts** - Allow customers enter what they want to pay.
* **Coupon Codes** - Setup discount codes in your Stripe dashboard for customers to apply to their total.
* **Custom Fields** - Record additional data along with each Stripe payment.
* **Subscriptions** - Let customers pay and sign up for your Stripe recurring plans.

[See Pricing & Demos](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

Here are a few shortcode examples (amounts in U.S. cents):

`[stripe name="The Awesome Store" amount="1999" description="The Awesome Blueprint Book"]`

`[stripe name="The Awesome Store" amount="1999" description="Five Awesome Coaching Calls"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" image_url="http://www.example.com/book_image.jpg"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" checkout_button_label="Now only {{amount}}!" enable_remember="false"]`

[Documentation & Getting Started](http://wpstripe.net/docs/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

[Shortcode Documentation](http://wpstripe.net/docs/shortcodes/stripe-checkout/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

Easily toggle between test and live mode until you're ready.

If you want your customers to receive email receipts, make sure you enable this setting in your Stripe dashboard.

[Learn More About Stripe Checkout Pro](http://wpstripe.net/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

###Feature Requests and Updates###

* [Public roadmap and feature requests](https://trello.com/b/neTGsBIY)
* [Get notified when new features are released](http://eepurl.com/YMXvP)
* [Follow this project on Github](https://github.com/pderksen/WP-Stripe-Checkout)

== Installation ==

There are three ways to install this plugin.

= 1. Admin Search =
1. In your Admin, go to menu Plugins > Add.
1. Search for `Stripe Checkout`.
1. Find the plugin that's labeled `Simple Stripe Checkout`.
1. Look for the author name `Phil Derksen` on the plugin.
1. Click to install.
1. Activate the plugin.
1. A new menu item `Simple Stripe Checkout` will appear in the main menu.

= 2. Download & Upload =
1. Download the plugin (a zip file) on the right column of this page.
1. In your Admin, go to menu Plugins > Add.
1. Select the tab "Upload".
1. Upload the .zip file you just downloaded.
1. Activate the plugin.
1. A new menu item `Simple Stripe Checkout` will appear in the main menu.

= 3. FTP Upload =
1. Download the plugin (.zip file) on the right column of this page.
1. Unzip the zip file contents.
1. Upload the `stripe` folder to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. A new menu item `Simple Stripe Checkout` will appear in the main menu.

== Frequently Asked Questions ==

[Detailed Shortcode Documentation](http://wpstripe.net/docs/shortcodes/stripe-checkout/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

[General Plugin Troubleshooting / FAQ](http://wpstripe.net/docs/simple-stripe-checkout-faq/?utm_source=wordpress_org&utm_medium=link&utm_campaign=stripe_checkout)

== Screenshots ==

1. Desktop checkout overlay
2. Mobile checkout overlay
3. Settings: Stripe keys
4. Settings: Site-wide defaults

== Changelog ==

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
