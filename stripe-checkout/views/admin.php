<?php

/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 *
 * @package    SC
 * @subpackage Views
 * @author     Phil Derksen <pderksen@gmail.com>, Nick Young <mycorsceb@gmail.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<div id="sc-settings">
		<div id="sc-settings-content">

			<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
			
			<form method="post" action="options.php">
					<?php
					
						settings_fields( 'sc_settings_general' );
						do_settings_sections( 'sc_settings_general' );
	
						submit_button();
					?>
				</form>
			
			<h2><?php _e( 'Shortcode Help', 'sc' ); ?></h2>
			
			<p>
				<?php _e( 'Use the shortcode', 'sc' ); ?> <code>[stripe]</code> <?php _e( 'to display the Stripe Checkout button within your content.', 'sc' ); ?>
			</p>
			<p>
				<?php _e( 'Use the function', 'sc' ); ?> <code><?php echo htmlentities( '<?php echo do_shortcode(\'[stripe]\'); ?>' ); ?></code>
				<?php _e( 'to display within template or theme files.', 'sc' ); ?>
			</p>

			<h4><?php _e( 'Available Attributes', 'sc' ); ?></h4>

			<table class="widefat importers" cellspacing="0">
				<thead>
					<tr>
						<th><?php _e( 'Attribute', 'sc' ); ?></th>
						<th><?php _e( 'Description', 'sc' ); ?></th>
						<th><?php _e( 'Default', 'sc' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>name</td>
						<td><?php _e( 'The name of your company or website.', 'sc' ) ?></td>
						<td>Site Title</td>
					</tr>
					<tr>
						<td>description</td>
						<td><?php _e( 'A description of the product or service being purchased.', 'sc' ); ?></td>
						<td><?php _e( 'n/a', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>amount</td>
						<td><?php printf( __( 'Amount in desired currency. Use smallest common currency unit (%s). U.S. amounts are in cents.', 'sc' ), 
					'<a href="https://support.stripe.com/questions/which-zero-decimal-currencies-does-stripe-support" target="_blank">read more</a>' ); ?></td>
						<td>100 (equals $1.00 US)</td>
					</tr>
					
					<tr>
						<td>image_url</td>
						<td><?php _e( 'A URL pointing to a square image of your brand or product. The recommended minimum size is 128x128px.', 'sc' ); ?></td>
						<td><?php _e( 'n/a', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>currency</td>
						<td><?php echo __( 'Specify a specific currency by using it\'s ', 'sc' ) . 
							'<a href="https://support.stripe.com/questions/which-currencies-does-stripe-support" target="_blank">' . 
							__( '3-letter ISO code.', 'sc' ) . '</a>'; ?></td>
						<td><?php _e( 'USD', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>checkout_button_label</td>
						<td><?php _e( 'The label of the payment button in the Checkout form. You can use {{amount}} to display the amount.', 'sc' ); ?></td>
						<td><?php _e( 'Pay {{amount}}', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>billing</td>
						<td><?php _e( 'Used to gather the billing address during the checkout process. (true or false)', 'sc' ); ?></td>
						<td><?php _e( 'false', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>shipping</td>
						<td><?php _e( 'Used to gather the shipping address during the checkout process. (true or false)', 'sc' ); ?></td>
						<td><?php _e( 'false', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>enable_remember</td>
						<td><?php _e( 'Adds a "remember me" checkbox to the checkout form. (true or false)', 'sc' ); ?></td>
						<td><?php _e( 'true', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>payment_button_label</td>
						<td><?php _e( 'Changes text on the default blue button that users click to initiate a checkout process.', 'sc' ); ?></td>
						<td><?php _e( 'Pay with Card', 'sc' ); ?></td>
					</tr>
					<tr>
						<td>success_redirect_url</td>
						<td><?php _e( 'The URL that the user should be redirected to after a successful payment.', 'sc' ); ?></td>
						<td><?php _e( 'Page payment made from', 'sc' ); ?></td>
					</tr>
				</tbody>
			</table>

			<p><strong><?php _e( 'Live transactions less than 50 cents (U.S.) are not allowed by Stripe.', 'sc' ); ?></strong></p>
			
			<h4><?php _e( 'Sample Shortcodes', 'sc' ); ?></h4>
			<ul class="ul-disc">
				<li><code>[stripe name="The Awesome Store" description="The Book of Awesomeness" amount="1999"]</code></li>
				<li><code>[stripe name="The Awesome Store" description="The Book of Awesomeness" amount="1999" shipping="true" billing="true"]</code></li>
				<li><code>[stripe name="The Awesome Store" description="The Book of Awesomeness" amount="1999" image_url="http://www.example.com/book_image.jpg"]</code></li>
				<li><code>[stripe name="The Awesome Store" description="The Book of Awesomeness" amount="1999" checkout_button_label="Now only {{amount}}!" enable_remember="false"]</code></li>
			</ul>

		</div><!-- #sc-settings-content -->

		<div id="sc-settings-sidebar">
			<?php include( 'admin-sidebar.php' ); ?>
		</div>

	</div>
</div><!-- .wrap -->
