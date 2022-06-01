<?php
/**
 * Customer Success: Achievements
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.6
 */

namespace SimplePay\Core\CustomerSuccess;

/**
 * CustomerAchievements class.
 *
 * @since 4.4.6
 */
class CustomerAchievements {

	/**
	 * Customer achievements option name.
	 *
	 * @since 4.4.6
	 * @var string
	 */
	const OPTION_NAME = 'simpay_customer_achievements';

	/**
	 * Retrive all achievements.
	 *
	 * @since 4.4.6
	 *
	 * @return array<string, int>
	 */
	public function get_all_achievements() {
		/** @var array<string, int> $achievements */
		$achievements = get_option( self::OPTION_NAME, array() );

		return $achievements;
	}

	/**
	 * Retrieves the completion timestamp for a given achievements, if available.
	 *
	 * @since 4.4.6
	 *
	 * @param string $achievement Achievement ID.
	 * @return int|null
	 */
	public function get_achievement( $achievement ) {
		$achievements = $this->get_all_achievements();

		if ( ! isset( $achievements[ $achievement ] ) ) {
			return null;
		}

		return $achievements[ $achievement ];
	}

	/**
	 * Adds an achievement completion.
	 *
	 * @since 4.4.6
	 *
	 * @param string $achievement Achievement slug.
	 * @return void
	 */
	public function add_achievement( $achievement ) {
		update_option(
			self::OPTION_NAME,
			array_merge(
				$this->get_all_achievements(),
				array(
					$achievement => time(),
				)
			)
		);
	}

}
