=== Simple Stripe Checkout ===
Contributors: pderksen, nickyoung87
Tags: stripe, stripe checkout, simple stripe checkout, ecommerce, e-commerce
Requires at least: 3.6.1
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Add a simple Stripe Checkout button and overlay to your site using a shortcode.

== Description ==

This plugin lets you bring in the Stripe Checkout right onto your site, so that transactions can happen inline — without ruining the browsing experience for your customers.

The overlay pops up the purchase form in a pretty lightbox.

Shortcode examples:

`[stripe name="demo" amount="1000"]`
`[stripe name="demo" description="demo description" amount="1000"]`

Full shortcode documentation is in Settings > Simple Stripe Checkout after plugin is activated.

[Follow this project on Github](https://github.com/pderksen/WP-Stripe-Checkout).

== Installation ==

= 1. Admin Search =
1. In your Admin, go to menu Plugins > Add.
1. Search for `Simple Stripe Checkout`.
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
1. Upload the `stripe-checkout` folder to the `/wp-content/plugins/` directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. A new menu item `Simple Stripe Checkout` will appear under your Settings menu option.

== Frequently Asked Questions ==

Full shortcode documentation is in Settings > Simple Stripe Checkout after plugin is activated.

Your theme must implement **wp_footer()** in the footer.php file, otherwise JavaScript will not load correctly. You can test if this is the issue by switching to a WordPress stock theme such as twenty-twelve temporarily.

If the overlay doesn't get triggered on click (and your browser is redirected to a stripe.com URL), please make sure that there is not extra code that is hijacking the click event (for example, a Google Analytics onclick event).

A popular known plugin that does this is "Google Analytics for WordPress". Try unchecking one or both of these options: 1) Track outbound clicks & downloads, 2) Check Advanced Settings, then make sure "Track outbound clicks as pageviews" is un-checked.

See the official Stripe checkout [documentation](https://stripe.com/docs/checkout) for further troubleshooting.

== Changelog ==

= 0.0.9 =

* Initial release.
