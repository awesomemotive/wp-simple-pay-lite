=== Simple Stripe Checkout ===
Contributors: pderksen, nickyoung87
Tags: stripe, stripe checkout, simple stripe checkout, ecommerce, e-commerce
Requires at least: 3.6.1
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The easiest way to add a high conversion Stripe Checkout form to your site and start getting paid.

== Description ==

Add a highly optimized Stripe Checkout form overlay to your site in a few simple steps.

Instead of spending time building your own checkout forms you can use Stripe's, which is continually tested for high conversion.

> "Stripe Checkout is an embeddable payment form for desktop, tablet, and mobile devices. It works within your site—customers can pay instantly, without being redirected away to complete the transaction."

Read more and see a demo at https://stripe.com/checkout.

###Plugin Requirements###

This is a simple standalone Stripe checkout plugin. That's it.

It does **NOT** require or integrate with:

* E-commerce plugins
* Membership site plugins
* Form building plugins

If you're using one of these, you can probably find a Stripe add-on designed for it.

But if all you need is a quick and easy standalone checkout form, Simple Stripe Checkout should do the job.

Note that Stripe suggests that the pages hosting the checkout form be SSL (start with `https://`). [Read more about SSL](https://stripe.com/help/ssl).

###Start Accepting Payments in 3 Easy Steps###

It only takes a couple minutes to add a payment form to your site.

1. Activate the plugin, go to Settings > Simple Stripe Checkout, then enter your Stripe keys.
1. Edit the post or page where you want the payment button and checkout form to appear.
1. Add a simple shortcode.

Viola! Now a payment button that opens your checkout form in an overlay will pop up.

Here are a few shortcode examples (amounts in U.S. cents):

`[stripe name="The Awesome Store" amount="1999" description="The Awesome Blueprint Book"]`

`[stripe name="The Awesome Store" amount="1999" description="Five Awesome Coaching Calls"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" image_url="http://www.example.com/book_image.jpg"]`

`[stripe name="The Awesome Store" amount="1999" description="The Book of Awesomeness" checkout_button_label="Now only {{amount}}!" enable_remember="false"]`

Easily toggle between test and live mode until you're ready.

If you want your customers to receive email receipts, make sure you enable this setting in your Stripe dashboard.

###Feature Requests and Updates###

* [Submit feature requests in our support forums.](https://wordpress.org/support/plugin/stripe)
* [Get notified of major updates.](http://eepurl.com/Q_ET9)
* [Follow this project on Github](https://github.com/pderksen/WP-Stripe-Checkout).

== Installation ==

= 1. Admin Search =
1. In your Admin, go to menu Plugins > Add.
1. Search for `Stripe Checkout`.
1. Find the plugin that's labeled `Simple Stripe Checkout`.
1. Look for the author name `Phil Derksen` on the plugin.
1. Click to install.
1. Activate the plugin.
1. A new menu item `Simple Stripe Checkout` will appear under your Settings menu option.

= 2. Download & Upload =
1. Download the plugin (a zip file) on the right column of this page.
1. In your Admin, go to menu Plugins > Add.
1. Select the tab "Upload".
1. Upload the .zip file you just downloaded.
1. Activate the plugin.
1. A new menu item `Simple Stripe Checkout` will appear under your Settings menu option.

= 3. FTP Upload =
1. Download the plugin (.zip file) on the right column of this page.
1. Unzip the zip file contents.
1. Upload the `stripe` folder to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. A new menu item `Simple Stripe Checkout` will appear under your Settings menu option.

== Frequently Asked Questions ==

**Where do I enter my Stripe keys and toggle between Test and Live modes?**

In your WordPress admin go to Settings > Simple Stripe Checkout after the plugin is activated.

**How do my customers get email receipts?**

This plugin does not email receipts through WordPress. Instead, we let Stripe take care of it.

To enable email receipts go to your Stripe dashboard > Account settings > Emails. You can configure business details and upload a logo here as well.

Note that you won't receive emails in Test mode.

**Where are my Stripe payment records?**

Unlike other form builder and e-commerce plugins, this plugin does not keep record of your transactions since Stripe already does this for you. Just visit your Stripe dashboard to view all your payments and related transactions.

**Is SSL (https://) required?**

*From the official [Stripe checkout documentation](https://stripe.com/docs/checkout):*

> All submissions of payment info using Checkout are made via a secure HTTPS connection. However, in order to protect yourself from certain forms of man-in-the-middle attacks, we suggest that you also serve the page containing the payment form with HTTPS as well. This means that any page that a Checkout form may exist on should start with `https://` rather than just `http://`.

> If you are not familiar with the process of buying SSL certificates and integrating them with your server to enable a secure HTTPS connection, please visit our [Help Page for SSL](https://stripe.com/help/ssl).

**What does the "Remember me everywhere" option on the checkout form do?**

Stripe now has "1-tap" payments to allow customers to optionally save their details. [Read more here](https://stripe.com/checkout#onetap)

**I need to add each payment to a Google spreadsheet, each customer to a mailing list, or perform some other custom action after each transaction.**

Try using [Zapier](https://zapier.com/app/explore?services=stripe) to connect Stripe with other services, or perform custom actions with Stripe's [webhooks](https://stripe.com/docs/webhooks).

= Feature Requests & Announcements =

* [Submit feature requests in our support forums.](https://wordpress.org/support/plugin/stripe)
* [Get notified of major updates.](http://eepurl.com/Q_ET9)

= General Troubleshooting =

Your theme must implement **wp_footer()** in the footer.php file, otherwise JavaScript will not load correctly. You can test if this is the issue by switching to a WordPress stock theme such as twenty-twelve temporarily.

If the overlay doesn't get triggered on click (and your browser is redirected to a stripe.com URL), please make sure that there is not extra code that is hijacking the click event (for example, a Google Analytics onclick event).

A popular known plugin that does this is "Google Analytics for WordPress". Try unchecking one or both of these options: 1) Track outbound clicks & downloads, 2) Check Advanced Settings, then make sure "Track outbound clicks as pageviews" is un-checked.

See the official [Stripe checkout documentation](https://stripe.com/docs/checkout) for further troubleshooting.

== Screenshots ==

1. Desktop checkout overlay
2. Mobile checkout overlay
3. Admin settings page

== Changelog ==

= 1.1.0 =

* Added image_url shortcode attribute.
* Added currency shortcode attribute.
* Added checkout_button_label shortcode attribute.
* Added payment_button_label shortcode attribute (thanks @enollo).
* Added billing shortcode attribute.
* Added shipping shortcode attribute.
* Added enable_remember shortcode attribute.
* Added success_redirect_url shortcode attribute.
* Updated to most recent Stripe library.
* Added a couple of action and filter hooks.
* Fixed bug where other instances of Stripe class were causing errors.
* Removed a default string being added to customer description in Stripe dashboard.

= 1.0.1 =

* Fixed bug where customers would not receive an email receipt after purchase.

= 1.0.0 =

* Initial release.
