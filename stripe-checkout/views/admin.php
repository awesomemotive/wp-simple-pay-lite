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
if ( ! defined( 'ABSPATH' ) )
	exit;
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
					<td><?php _e( 'The amount (in U.S. cents) that\'s shown to the user.', 'sc' ); ?></td>
					<td>100 (equals $1.00 US)</td>
				</tr>
				</tbody>
			</table>

			<p style="color: red;"><strong><?php _e( 'Note that the amount is in U.S. cents. Do not enter a decimal separator.', 'sc' ); ?></strong></p>
			
			<h4><?php _e( 'Sample Shortcode', 'sc' ); ?></h4>

			<code>[stripe name="The Awesome Store" description="The Book of Awesomeness" amount="1999"]</code>

		</div><!-- #sc-settings-content -->
	</div>

</div><!-- .wrap -->
