<?php
/**
 * Customer Success: First form
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
 * FirstForm class.
 *
 * @since 4.4.6
 */
class FirstForm implements SubscriberInterface {

	const ACHIEVEMENT = 'first-form';

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
			'save_post' => array( 'first_form', 10, 2 ),
		);
	}

	/**
	 * Adds the first form achievement when publishing a payment form.
	 *
	 * @since 4.4.6
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post Post object.
	 * @return void
	 */
	public function first_form( $post_id, $post ) {
		// Not a payment form.
		if ( 'simple-pay' !== $post->post_type ) {
			return;
		}

		// Achievement already occured.
		if ( null !== $this->achievements->get_achievement( self::ACHIEVEMENT ) ) {
			return;
		}

		$this->achievements->add_achievement( self::ACHIEVEMENT );
	}

}
