<?php

namespace SimplePay\Core\Admin\Pages;

use SimplePay\Core\Admin\Tables\Form_List_Table;
use SimplePay\Core\Admin\Metaboxes\Settings;
use SimplePay\Core\Forms\Default_Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Main {

	public static $post_status = null;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

	}

	// Main html output callback function
	public static function html() {

		if ( isset( $_REQUEST['form_id'] ) ) {

			$id = absint( $_REQUEST['form_id'] );

			$simpay_form = apply_filters( 'simpay_form_view', '', $id );

			if ( empty( $simpay_form ) ) {
				new Default_Form( $id );
			}

		}

		// Check if we need to save
		if ( ( isset( $_POST['save'] ) || isset( $_POST['save_draft'] ) ) && isset( $_POST['simpay_form_id'] ) ) {

			self::save_post( absint( $_POST['simpay_form_id'] ) );
		}

		ob_start();
		?>

		<div class="wrap"><!-- #start .wrap -->

			<?php
			if ( isset( $_GET['action'] ) ) {

				self::handle_messages();

				self::get_form_settings( $_GET['action'] );
			} else {
				self::main_page();
			}

			?>
		</div><!-- #end .wrap -->

		<?php
		ob_end_flush();
	}

	/**
	 * Handle the admin messages based on what action took place
	 */
	public static function handle_messages() {

		if ( ! empty( self::$post_status ) ) {

			switch ( self::$post_status ) {

				case 'updated':
					$message = __( 'Form Updated.', 'stripe' );
					$class   = 'updated';
					break;
				case 'draft':
					$message = __( 'Draft saved.', 'stripe' );
					$class   = 'updated';
					break;
				default:
					// Default to error message
					$class   = 'error';
					$message = 'An error occurred.';
			}

			echo '<div id="message" class="' . esc_attr( $class ) . '"><p>' . $message . '</p></div>';

			self::$post_status = null;
		}
	}

	/**
	 * Save the form
	 *
	 * @param $post_id
	 */
	public static function save_post( $post_id ) {

		$title = isset( $_POST['post_title'] ) ? sanitize_text_field( $_POST['post_title'] ) : '';

		$status = isset( $_POST['save_draft'] ) ? 'draft' : 'publish';

		// This doesn't actually insert a post, but updates the title of the post with this ID
		$post_id = wp_insert_post( array(
			'ID'          => absint( $post_id ),
			'post_title'  => $title,
			'post_type'   => 'simple-pay',
			'post_status' => $status,
		) );

		if ( 0 !== $post_id && ! is_wp_error( $post_id ) ) {
			$post = get_post( $post_id );


			Settings::save( $post_id, $post );

			if ( 'draft' === $status ) {
				self::$post_status = 'draft';
			} elseif ( 'publish' === $status ) {
				self::$post_status = 'updated';
			}
		} else {
			self::$post_status = 'error';
		}
	}

	/**
	 * Get the title of the page with the "Add New" button next to it
	 *
	 * @param $title
	 */
	public static function get_title( $title ) {

		$url = add_query_arg( array(
			'action' => 'create',
		), admin_url( 'admin.php?page=simpay' ) );

		?>
		<h1 id="simpay-forms" class="wp-heading-inline"><?php echo $title; ?></h1>
		<a href="<?php echo esc_attr( $url ); ?>" class="page-title-action"><?php esc_html_e( 'Add New', 'stripe' ); ?></a>
		<hr class="wp-header-end">
		<?php
	}

	/**
	 * Output for the main page content (forms list table)
	 */
	public static function main_page() {

		self::get_title( esc_html__( 'Payment Forms', 'stripe' ) );

		echo '<form method="get">';
		// Get our form table to output form CPT posts
		$form_table = new Form_List_Table();

		$form_table->prepare_items();
		$form_table->views();
		$form_table->display();

		echo '</form>';
	}

	/**
	 * Get the output of the form settings metaboxes
	 *
	 * @param $action
	 *
	 * @return string
	 */
	public static function get_form_settings( $action ) {

		global $post;

		switch ( $action ) {
			case 'edit':
				{
					$id   = absint( $_GET['form_id'] );
					$form = get_post( $id );

					$form_action = '';

					self::get_title( esc_html__( 'Edit Payment Form', 'stripe' ) );

					break;
				}

			case 'create':
				{
					// Add New is actually creating the payment form CPT record here.

					// Create post object
					$form_args = array(
						'post_title'   => '',
						'post_content' => '',
						'post_status'  => 'draft',
						'post_type'    => 'simple-pay',
					);
					
					// Insert the post into the database
					$form_id = wp_insert_post( $form_args );

					$form = get_post( $form_id );

					do_action( 'simpay_form_created', $form->ID );

					$form_action = esc_url( add_query_arg( array(
						'action'  => 'edit',
						'form_id' => $form->ID,
					), admin_url( 'admin.php?page=simpay' ) ) );

					self::get_title( esc_html__( 'Add New Payment Form', 'stripe' ) );

					break;
				}

			default:
				{
					self::main_page();

					return '';
				}
		}

		$post = $form;

		setup_postdata( $post );
		?>

		<form id="post" method="post" action="<?php echo esc_attr( $form_action ); ?>">
			<div id="poststuff">

				<div id="post-body" class="metabox-holder columns-2">

					<!-- Post body content -->
					<div id="post-body-content">
						<div id="titlediv">
							<div id="titlewrap">
								<label class="screen-reader-text" id="title-prompt-text" for="title"><?php esc_html_e( 'Enter title here', 'stripe' ); ?></label>
								<input type="text" name="post_title" size="30" id="title" value="<?php echo esc_attr( $form->post_title ); ?>" spellcheck="true" autocomplete="off" placeholder="<?php esc_attr_e( 'Enter title here', 'stripe' ); ?>">
							</div>
							<br class="clear">
						</div>

						<div id="simpay-form-settings" class="postbox">
							<div class="inside">
								<input type="hidden" name="simpay_form_id" value="<?php echo esc_attr( $form->ID ); ?>" />

								<?php

								Settings::html( $post );

								?>
							</div>
						</div>
					</div>

					<div id="postbox-container-1" class="postbox-container">
						<?php

						self::get_publish_metabox( $form, $action );
						//self::get_shortcode_metabox( $form );

						?>
					</div>

				</div>
		</form>
		<?php
	}

	/**
	 * Our custom publish metabox output
	 *
	 * @param        $form
	 * @param string $action
	 */
	public static function get_publish_metabox( $form, $action = '' ) {

		$button_text = '';

		if ( 'create' === $action ) {
			$button_text = esc_html__( 'Create', 'stripe' );
		} elseif ( 'edit' === $action ) {
			$button_text = esc_html__( 'Update', 'stripe' );
		}

		$is_draft = ( 'draft' === get_post_status( $form->ID ) ? true : false );

		?>
		<!-- Publish Metabox -->
		<div id="submitdiv" class="postbox">
			<div class="inside">
				<div class="submitbox" id="submitpost">

					<div id="minor-publishing">

						<div id="minor-publishing-actions">

							<?php if ( $is_draft ) { // Show only if this is already a draft ?>
								<div id="save-action">
									<input type="submit" name="save_draft" id="save-post" value="<?php esc_attr_e( 'Save Draft', 'simple-pay' ); ?>" class="simpay-button button">
									<span class="spinner"></span>
								</div>
							<?php } ?>

							<div id="preview-action">
								<?php
								// Build the preview link
								$preview_link = add_query_arg( array(
									'simpay-preview' => $form->ID,
								), site_url() );
								?>
								<input type="submit" value="<?php esc_attr_e( 'Preview', 'stripe' ); ?>" id="simpay-preview-button" class="simpay-button button"
								       title="<?php esc_html_e( 'Preview saved changes', 'stripe' ); ?>" data-action="<?php echo esc_url( $preview_link ); ?>" />
							</div>
							<div class="clear"></div>

						</div><!-- #minor-publishing-actions -->

						<div id="misc-publishing-actions">

							<div class="misc-pub-section misc-pub-post-status">
								<?php esc_html_e( 'Status:', 'stripe' ); ?>
								<span id="post-status-display"><?php echo self::get_readable_post_status( $form ); ?></span>
							</div><!-- .misc-pub-section -->

							<div class="misc-pub-section">
								<?php self::get_shortcode_metabox( $form ); ?>
							</div>
						</div>
						<div class="clear"></div>

					</div>

					<div id="major-publishing-actions">
						<div id="delete-action">
							<?php
							// delete nonce
							$delete_nonce = wp_create_nonce( 'simpay_trash_form' );

							$delete_link = add_query_arg( array(
								'simpay_form' => $form->ID,
								'_wpnonce'    => $delete_nonce,
							), admin_url( 'admin.php?page=simpay&action=trash' ) );
							?>
							<a class="submitdelete deletion" href="<?php echo esc_url( $delete_link ); ?>"><?php esc_html_e( 'Move to Trash', 'stripe' ); ?></a>
						</div>

						<div id="publishing-action">
							<span class="spinner"></span>
							<input name="save" type="submit" class="simpay-button button button-primary button-large" id="publish" value="<?php echo esc_attr( $button_text ); ?>">
						</div>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div> <!-- End publish metabox -->
		<?php
	}

	/**
	 * Our custom shortcode metabox output
	 *
	 * @param $form
	 */
	public static function get_shortcode_metabox( $form ) {
		?>
		<!-- Form Shortcode Box -->
		<div id="simpay-get-shortcode">
			<label for="simpay-shortcode"><?php esc_html_e( 'Payment Form Shortcode', 'stripe' ); ?>:</label>
			<?php simpay_print_shortcode_tip( $form->ID ); ?>
		</div> <!-- End form shortcode metabox -->
		<?php
	}

	/**
	 * Get the more friendly output of the post status i.e. instead of "Publish" it will say "Published"
	 *
	 * @param $post
	 *
	 * @return string
	 */
	public static function get_readable_post_status( $post ) {

		$status = get_post_status( $post );

		switch ( $status ) {
			case 'draft':
				return esc_html__( 'Draft', 'stripe' );
			case 'publish':
				return esc_html__( 'Published', 'stripe' );
			default:
				return '';
		}
	}

}
