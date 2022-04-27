<?php
/**
 * Admin: "About Us" page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.0
 */

namespace SimplePay\Core\AdminPage;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * AboutUsPage class.
 *
 * @since 4.4.0
 */
class AboutUsPage extends AbstractAdminPage implements AdminSecondaryPageInterface, LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * {@inheritdoc}
	 */
	public function get_position() {
		return 99;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_capability_requirement() {
		return 'manage_options';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_menu_title() {
		return __( 'About Us', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_title() {
		return __( 'About Us', 'stripe' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_page_slug() {
		return 'simpay-about-us';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_parent_slug() {
		return 'edit.php?post_type=simple-pay';
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_block_editor() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render() {
		$available_tabs = array( 'about-us', 'getting-started', 'lite-vs-pro' );
		$active_tab     = (
			isset( $_GET['view'] ) &&
			in_array( $_GET['view'], $available_tabs, true )
		)
			? sanitize_text_field( $_GET['view'] )
			: 'about-us';

		$base_url = add_query_arg(
			array(
				'post_type' => 'simple-pay',
				'page'      => 'simpay-about-us',
			),
			admin_url( 'edit.php' )
		);

		$tabs = array(
			'about-us'        => __( 'About Us', 'stripe' ),
			'getting-started' => __( 'Getting Started', 'stripe' ),
		);

		// Assets.
		// @todo Use ScriptLoader and StyleLOader
		wp_enqueue_style(
			'simpay-fontawesome',
			'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'
		);

		switch ( $active_tab ) {
			case 'about-us':
				$am_plugins          = $this->get_am_plugins();
				$can_install_plugins = current_user_can( 'install_plugins' );
				break;
			case 'getting-started':
				$is_lite      = $this->license->is_lite();
				$upgrade_url  = simpay_pro_upgrade_url( 'about-us' );
				$upgrade_text = esc_html__( 'Upgrade to WP Simple Pay Pro', 'stripe' );
		}

		$license = $this->license;

		// @todo use a ViewLoader
		include_once SIMPLE_PAY_DIR . '/views/admin-page-about-us.php'; // @phpstan-ignore-line
	}

	/**
	 * Returns a list of Awesome Motive plugins and related information.
	 *
	 * @since 4.4.0
	 *
	 * @return array<mixed>
	 */
	private function get_am_plugins() {
		/** @var array<string> $all_plugins */
		$all_plugins = get_plugins();
		$images_url  = SIMPLE_PAY_INC_URL . '/core/assets/images/about/'; // @phpstan-ignore-line
		$am_plugins  = array(
			// AffiliateWP.
			'affiliatewp/affiliate-wp.php'                 => array(
				'icon'  => $images_url . 'plugin-affwp.png',
				'name'  => esc_html__( 'AffiliateWP', 'stripe' ),
				'desc'  => esc_html__( 'The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'stripe' ),
				'wporg' => '',
				'url'   => 'https://www.affiliatewp.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
				'act'   => 'go-to-url',
			),

			// OptinMonster.
			'optinmonster/optin-monster-wp-api.php'        => array(
				'icon'  => $images_url . 'plugin-om.png',
				'name'  => esc_html__( 'OptinMonster', 'stripe' ),
				'desc'  => esc_html__( 'Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/optinmonster/',
				'url'   => 'https://downloads.wordpress.org/plugin/optinmonster.zip',
			),

			// MonsterInsights.
			'google-analytics-for-wordpress/googleanalytics.php' => array(
				'icon'  => $images_url . 'plugin-mi.png',
				'name'  => esc_html__( 'MonsterInsights', 'stripe' ),
				'desc'  => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/google-analytics-for-wordpress/',
				'url'   => 'https://downloads.wordpress.org/plugin/google-analytics-for-wordpress.zip',
				'pro'   => array(
					'plug' => 'google-analytics-premium/googleanalytics-premium.php',
					'icon' => $images_url . 'plugin-mi.png',
					'name' => esc_html__( 'MonsterInsights Pro', 'stripe' ),
					'desc' => esc_html__( 'The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'stripe' ),
					'url'  => 'https://www.monsterinsights.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// WP Mail SMTP.
			'wp-mail-smtp/wp_mail_smtp.php'                => array(
				'icon'  => $images_url . 'plugin-smtp.png',
				'name'  => esc_html__( 'WP Mail SMTP', 'stripe' ),
				'desc'  => esc_html__( "Improve your WordPress email deliverability and make sure that your website emails reach user's inbox with the #1 SMTP plugin for WordPress. Over 2 million websites use it to fix WordPress email issues.", 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/wp-mail-smtp/',
				'url'   => 'https://downloads.wordpress.org/plugin/wp-mail-smtp.zip',
				'pro'   => array(
					'plug' => 'wp-mail-smtp-pro/wp_mail_smtp.php',
					'icon' => $images_url . 'plugin-smtp.png',
					'name' => esc_html__( 'WP Mail SMTP Pro', 'stripe' ),
					'desc' => esc_html__( "Improve your WordPress email deliverability and make sure that your website emails reach user's inbox with the #1 SMTP plugin for WordPress. Over 2 million websites use it to fix WordPress email issues.", 'stripe' ),
					'url'  => 'https://wpmailsmtp.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// All in One SEO Pack.
			'all-in-one-seo-pack/all_in_one_seo_pack.php'  => array(
				'icon'  => $images_url . 'plugin-aioseo.png',
				'name'  => esc_html__( 'AIOSEO', 'stripe' ),
				'desc'  => esc_html__( "The original WordPress SEO plugin and toolkit that improves your website's search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.", 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/all-in-one-seo-pack/',
				'url'   => 'https://downloads.wordpress.org/plugin/all-in-one-seo-pack.zip',
				'pro'   => array(
					'plug' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
					'icon' => $images_url . 'plugin-aioseo.png',
					'name' => esc_html__( 'AIOSEO Pro', 'stripe' ),
					'desc' => esc_html__( "The original WordPress SEO plugin and toolkit that improves your website's search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.", 'stripe' ),
					'url'  => 'https://aioseo.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// SeedProd.
			'coming-soon/coming-soon.php'                  => array(
				'icon'  => $images_url . 'plugin-seedprod.png',
				'name'  => esc_html__( 'SeedProd', 'stripe' ),
				'desc'  => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/coming-soon/',
				'url'   => 'https://downloads.wordpress.org/plugin/coming-soon.zip',
				'pro'   => array(
					'plug' => 'seedprod-coming-soon-pro-5/seedprod-coming-soon-pro-5.php',
					'icon' => $images_url . 'plugin-seedprod.png',
					'name' => esc_html__( 'SeedProd Pro', 'stripe' ),
					'desc' => esc_html__( 'The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'stripe' ),
					'url'  => 'https://www.seedprod.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// RafflePress.
			'rafflepress/rafflepress.php'                  => array(
				'icon'  => $images_url . 'plugin-rp.png',
				'name'  => esc_html__( 'RafflePress', 'stripe' ),
				'desc'  => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/rafflepress/',
				'url'   => 'https://downloads.wordpress.org/plugin/rafflepress.zip',
				'pro'   => array(
					'plug' => 'rafflepress-pro/rafflepress-pro.php',
					'icon' => $images_url . 'plugin-rp.png',
					'name' => esc_html__( 'RafflePress Pro', 'stripe' ),
					'desc' => esc_html__( 'Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'stripe' ),
					'url'  => 'https://rafflepress.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// SearchWP.
			'searchwp/searchwp.php'                        => array(
				'icon'  => $images_url . 'plugin-searchwp.png',
				'name'  => esc_html__( 'SearchWP', 'stripe' ),
				'desc'  => esc_html__( 'The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'stripe' ),
				'wporg' => '',
				'url'   => 'https://www.searchwp.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
				'act'   => 'go-to-url',
			),

			// PushEngage.
			'pushengage/main.php'                          => array(
				'icon'  => $images_url . 'plugin-pushengage.png',
				'name'  => esc_html__( 'PushEngage', 'stripe' ),
				'desc'  => esc_html__( 'Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 9 billion notifications each month.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/pushengage/',
				'url'   => 'https://downloads.wordpress.org/plugin/pushengage.zip',
			),

			// Smash Balloon (Facebook).
			'custom-facebook-feed/custom-facebook-feed.php' => array(
				'icon'  => $images_url . 'plugin-sb-fb.png',
				'name'  => esc_html__( 'Smash Balloon Facebook Feeds', 'stripe' ),
				'desc'  => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/custom-facebook-feed/',
				'url'   => 'https://downloads.wordpress.org/plugin/custom-facebook-feed.zip',
				'pro'   => array(
					'plug' => 'custom-facebook-feed-pro/custom-facebook-feed.php',
					'icon' => $images_url . 'plugin-sb-fb.png',
					'name' => esc_html__( 'Smash Balloon Facebook Feeds Pro', 'stripe' ),
					'desc' => esc_html__( 'Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'stripe' ),
					'url'  => 'https://smashballoon.com/custom-facebook-feed/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// Smash Balloon (YouTube).
			'feeds-for-youtube/youtube-feed.php'           => array(
				'icon'  => $images_url . 'plugin-sb-youtube.png',
				'name'  => esc_html__( 'Smash Balloon YouTube Feeds', 'stripe' ),
				'desc'  => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/feeds-for-youtube/',
				'url'   => 'https://downloads.wordpress.org/plugin/feeds-for-youtube.zip',
				'pro'   => array(
					'plug' => 'youtube-feed-pro/youtube-feed.php',
					'icon' => $images_url . 'plugin-sb-youtube.png',
					'name' => esc_html__( 'Smash Balloon YouTube Feeds Pro', 'stripe' ),
					'desc' => esc_html__( 'Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'stripe' ),
					'url'  => 'https://smashballoon.com/youtube-feed/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// Trust Pulse.
			'trustpulse-api/trustpulse.php'                => array(
				'icon'  => $images_url . 'plugin-trustpulse.png',
				'name'  => esc_html__( 'TrustPulse', 'stripe' ),
				'desc'  => esc_html__( 'Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/trustpulse-api/',
				'url'   => 'https://downloads.wordpress.org/plugin/trustpulse-api.zip',
			),

			// Easy Digital Downloads
			'easy-digital-downloads/easy-digital-downloads.php' => array(
				'icon'  => $images_url . 'plugin-edd.png',
				'name'  => esc_html__( 'Easy Digital Downloads', 'stripe' ),
				'desc'  => esc_html__( 'The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/easy-digital-downloads/',
				'url'   => 'https://downloads.wordpress.org/plugin/easy-digital-downloads.zip',
			),

			// Sugar Calendar.
			'sugar-calendar-lite/sugar-calendar-lite.php' => array(
				'icon'  => $images_url . 'plugin-sugarcalendar.png',
				'name'  => esc_html__( 'Sugar Calendar', 'stripe' ),
				'desc'  => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/sugar-calendar-lite/',
				'url'   => 'https://downloads.wordpress.org/plugin/sugar-calendar-lite.zip',
				'pro'   => array(
					'plug' => 'sugar-calendar/sugar-calendar.php',
					'icon' => $images_url . 'plugin-sugarcalendar.png',
					'name'  => esc_html__( 'Sugar Calendar Pro', 'stripe' ),
					'desc'  => esc_html__( 'A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'stripe' ),
					'url'  => 'https://sugarcalendar.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),

			// WPForms
			'wpforms-lite/wpforms.php' => array(
				'icon'  => $images_url . 'plugin-wpforms.png',
				'name'  => esc_html__( 'WPForms', 'stripe' ),
				'desc'  => esc_html__( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 100+ form templates. Trusted by over 4 million websites as the best forms plugin.', 'stripe' ),
				'wporg' => 'https://wordpress.org/plugins/wpforms-lite/',
				'url'   => 'https://downloads.wordpress.org/plugin/wpforms-lite.zip',
				'pro'   => array(
					'plug' => 'wpforms/wpforms.php',
					'icon' => $images_url . 'plugin-wpforms.png',
					'name'  => esc_html__( 'WPForms Pro', 'stripe' ),
					'desc'  => esc_html__( 'The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 100+ form templates. Trusted by over 4 million websites as the best forms plugin.', 'stripe' ),
					'url'  => 'https://wpforms.com/?utm_source=wpsimplepay-plugin&utm_medium=link&utm_campaign=about-wpsimplepay',
					'act'  => 'go-to-url',
				),
			),
		);

		foreach ( $am_plugins as $plugin_name => $details ) {
			$am_plugins[ $plugin_name ] = $this->get_plugin_data(
				$plugin_name,
				$details,
				$all_plugins
			);
		}

		return $am_plugins;
	}

	/**
	 * Retrieves AM plugin data to display in the Addons section of About tab.
	 *
	 * @since 4.4.0
	 *
	 * @param string        $plugin Plugin slug.
	 * @param array<mixed>  $details Plugin details.
	 * @param array<string> $all_plugins List of all plugins.
	 * @return array<mixed>
	 */
	private function get_plugin_data( $plugin, $details, $all_plugins ) {
		$show_pro    = false;
		$plugin_data = array();

		if (
			isset( $details['pro'] ) &&
			is_array( $details['pro'] ) &&
			isset( $details['pro']['plug'] )
		) {
			if ( array_key_exists( $plugin, $all_plugins ) ) {
				if ( is_plugin_active( $plugin ) ) {
					$show_pro = true;
				}
			}

			if ( array_key_exists( $details['pro']['plug'], $all_plugins ) ) {
				$show_pro = true;
			}

			if ( true === $show_pro ) {
				$plugin  = $details['pro']['plug'];
				$details = $details['pro'];
			}
		}

		if ( array_key_exists( $plugin, $all_plugins ) ) {
			if ( is_plugin_active( $plugin ) ) {
				// Status text/status.
				$plugin_data['status_class'] = 'status-active';
				$plugin_data['status_text']  = esc_html__( 'Active', 'stripe' );

				// Button text/status.
				$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-secondary disabled';
				$plugin_data['action_text']  = esc_html__( 'Activated', 'stripe' );
				$plugin_data['plugin_src']   = esc_attr( $plugin );
			} else {
				// Status text/status.
				$plugin_data['status_class'] = 'status-installed';
				$plugin_data['status_text']  = esc_html__( 'Inactive', 'stripe' );

				// Button text/status.
				$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-secondary';
				$plugin_data['action_text']  = esc_html__( 'Activate', 'stripe' );
				$plugin_data['plugin_src']   = esc_attr( $plugin );
			}
		} else {
			// Doesn't exist, install.
			// Status text/status.
			$plugin_data['status_class'] = 'status-missing';

			if ( isset( $details['act'] ) && 'go-to-url' === $details['act'] ) {
				$plugin_data['status_class'] = 'status-go-to-url';
			}

			$plugin_data['status_text'] = esc_html__( 'Not Installed', 'stripe' );

			// Button text/status.
			$plugin_data['action_class'] = $plugin_data['status_class'] . ' button button-primary';
			$plugin_data['action_text']  = esc_html__( 'Install Plugin', 'stripe' );
			if ( isset( $details['url'] ) ) {
				/** @var string $url */
				$url = $details['url'];

				$plugin_data['plugin_src'] = esc_url( $url );
			}
		}

		$plugin_data['details'] = $details;

		return $plugin_data;
	}

}
