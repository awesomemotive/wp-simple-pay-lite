<?php
/**
 * Simple Pay: Edit form Subscription options
 *
 * @package SimplePay\Core\Post_Types\Simple_Pay\Edit_Form
 * @copyright Copyright (c) 2020, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.8.0
 */

namespace SimplePay\Core\Post_Types\Simple_Pay\Edit_Form;

use function SimplePay\Core\Admin\Notices\Promos\bfcm_is_promo_active;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds "Subscription Options" Payment Form settings tab content.
 *
 * Lite uses this as a promotional area.
 *
 * @since 3.8.0
 *
 * @param int $post_id Current Payment Form ID.
 */
function add_subscription_options( $post_id ) {
	// Get the upgrade URL.
	$upgrade_url = simpay_pro_upgrade_url( 'under-box-promo' );

	// Adjust the upgrade URL if there's an active promotion.
	$utm_args = array(
		'utm_source'   => 'form-settings',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => 'bfcm2019',
		'utm_content'  => 'upgrade-promo-subscription-options',
	);

	$upgrade_url = add_query_arg( $utm_args, 'https://wpsimplepay.com/lite-vs-pro/' );
	?>

<div class="simpay-promo-under-box">

	<h2><?php _e( 'Need your customers to sign up for recurring payments?', 'stripe' ); ?></h2>

	<p>
		<?php _e( 'By upgrading to a WP Simple Pay Pro Plus or higher license, you can connect Stripe subscriptions to your payment forms. You can also create installment plans, setup fees, and free trial periods.', 'stripe' ); ?>
	</p>

	<?php if ( true === bfcm_is_promo_active() ) : ?>
		<p>
			<?php _e( 'Upgrade before <em>23:59 PM December 6th CST</em> and <strong>SAVE 25%</strong> during our Black Friday & Cyber Monday sale. Use code <code>BFCM2019</code> at checkout.', 'stripe' ); ?>
		</p>
	<?php endif; ?>

	<p>
		<a
			href="<?php echo esc_url( $upgrade_url ); ?>"
			class="button simpay-upgrade-btn simpay-upgrade-btn-large"
			target="_blank"
			rel="noopener noreferrer"
		>
			<?php _e( 'Click here to Upgrade', 'stripe' ); ?>
		</a>
	</p>

</div>

	<?php
}
add_action( 'simpay_form_settings_meta_subscription_display_panel', __NAMESPACE__ . '\\add_subscription_options' );
