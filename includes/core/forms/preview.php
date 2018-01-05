<?php

namespace SimplePay\Core\Forms;

use SimplePay\Core\Admin\Metaboxes\Settings;

/**
 * Class to handle the preview output of a form in progress
 *
 * @package SimplePay\Forms
 */
class Preview {

	public $preview_form_id = null;

	/**
	 * Preview constructor.
	 */
	public function __construct() {

		if ( isset( $_GET['src'] ) && 'btn' == $_GET['src'] ) {
			// Set the id to the saved post id for previewing
			$this->preview_form_id = get_option( 'simpay_preview_form_id' );

			$post = get_post( $this->preview_form_id );

			Settings::save( $this->preview_form_id, $post );

			// We don't want a warning about resubmitting post data when refreshing the page so we redirect with our new data and remove the btn src arg
			wp_redirect( add_query_arg( 'simpay-preview', $this->preview_form_id, site_url() ) );
			exit;
		} else {
			$this->preview_form_id = absint( $_GET['simpay-preview'] );
		}

		if ( empty( $this->preview_form_id ) ) {
			return;
		}

		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		add_filter( 'the_title', array( $this, 'the_title' ) );
		remove_filter( 'the_content', 'wpautop' );
		remove_filter( 'the_excerpt', 'wpautop' );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'get_the_excerpt', array( $this, 'the_content' ) );
		add_filter( 'template_include', array( $this, 'template_include' ) );
		add_filter( 'post_thumbnail_html', array( $this, 'post_thumbnail_html' ) );
	}

	/**
	 * Since we know we are on a preview page we set the posts_per_page to show only 1 (ours)
	 *
	 * @param $query
	 */
	public function pre_get_posts( $query ) {
		$query->set( 'posts_per_page', 1 );
	}

	/**
	 * Change the title of our preview page
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public function the_title( $title ) {
		if ( ! in_the_loop() ) {
			return $title;
		}

		return __( 'Simple Pay Preview', 'stripe' );
	}

	/**
	 * Rewrite the_content to output our form preview shortcode
	 *
	 * @param $content
	 *
	 * @return string
	 */
	public function the_content( $content ) {
		if ( ! is_user_logged_in() ) {
			return __( 'You must be logged in to preview a form.', 'stripe' );
		}

		if ( in_the_loop() ) {
			return do_shortcode( '[simpay_preview id="' . absint( $this->preview_form_id ) . '"]' );
		}

		return $content;
	}

	/**
	 * Search for these templates so we can show preview within the theme
	 *
	 * @return string
	 */
	public function template_include() {
		return locate_template( array( 'page.php', 'single.php', 'index.php' ) );
	}

	/**
	 * Hide any post thumbnails
	 *
	 * @return string
	 */
	public function post_thumbnail_html() {
		return '';
	}
}
