<?php
/**
 * Customer Success: First form embed in a post or page
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\CustomerSuccess\Achievement;

use SimplePay\Core\CustomerSuccess\CustomerAchievements;
use SimplePay\Core\EventManagement\SubscriberInterface;

/**
 * FirstFormEmbed class.
 *
 * @since 4.4.6
 */
class FirstFormEmbed implements SubscriberInterface {

	const ACHIEVEMENT = 'first-form-embed';

	/**
	 * Customer achievements.
	 *
	 * @since 4.4.6
	 * @var \SimplePay\Core\CustomerSuccess\CustomerAchievements
	 */
	private $achievements;

	/**
	 * FirstFormEmbedAchievement.
	 *
	 * @since 4.4.6
	 */
	public function __construct( CustomerAchievements $achievements ) {
		$this->achievements = $achievements;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_subscribed_events() {
		return array(
			'save_post'              => array(
				'first_form_embed_classic_editor', 10, 2
			),
			'rest_after_insert_page' => 'first_form_embed_block_editor',
			'rest_after_insert_post' => 'first_form_embed_block_editor',
		);
	}

	/**
	 * Adds the first form embed achievement when publishing a page with the classic editor,
	 * and the page content includes a payment form.
	 *
	 * @since 4.4.6
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function first_form_embed_classic_editor( $post_id, $post ) {
		// Not a post or page.
		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return;
		}

		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		// Content does not contain the payment form shortcode.
		if ( ! has_shortcode( $post->post_content, 'simpay' ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

	/**
	 * Adds the first form embed achievement when publishing a page with the block editor,
	 * and the page content includes a payment form.
	 *
	 * @since 4.4.6
	 *
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function first_form_embed_block_editor( $post ) {
		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		// WordPress 5.0+ only.
		if ( ! function_exists( 'has_block' ) ) {
			return;
		}

		// Content does not contain the payment form shortcode.
		if ( ! has_block( 'simpay/payment-form', $post ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

}
