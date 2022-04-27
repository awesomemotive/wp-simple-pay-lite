<?php
/**
 * Notification inbox: Notification rule processor
 *
 * Checks to see if the environment matches the passed conditions.
 * Supported conditions include:
 *
 * - Plugin version number -- either specific versions or wildcards (e.g. "4.4.4" or "4.x").
 * - License level -- lite, personal, plus, professional, ultimate, elite
 *
 * @todo support WordPress version, PHP version, etc.
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2022, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.4.5
 */

namespace SimplePay\Core\NotificationInbox;

use SimplePay\Core\License\LicenseAwareInterface;
use SimplePay\Core\License\LicenseAwareTrait;

/**
 * NotificationRuleProcessor class.
 *
 * @since 4.4.5
 */
class NotificationRuleProcessor implements LicenseAwareInterface {

	use LicenseAwareTrait;

	/**
	 * Determines if all conditions have been met.
	 *
	 * @since 4.4.5
	 *
	 * @param array<string> $conditions Conditions to check.
	 * @return bool
	 */
	public function is_valid( $conditions ) {
		// No conditions, always show.
		if ( empty( $conditions ) ) {
			return true;
		}

		$license_conditions = $this->get_license_conditions( $conditions );
		$version_conditions = $this->get_version_conditions( $conditions );

		// First ensure we have a corresponding license level.
		if ( false === $this->is_valid_license_level( $license_conditions ) ) {
			return false;
		}

		// Then check version conditions. Only needs to meet one.
		$matching_versions = array_filter(
			$version_conditions,
			function( $condition ) {
				return $this->is_version_number_match(
					SIMPLE_PAY_VERSION, // @phpstan-ignore-line
					$condition
				);
			}
		);

		if ( ! empty( $version_conditions ) && empty( $matching_versions ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns only license-level conditions.
	 *
	 * @since 4.4.5
	 *
	 * @param array<string> $conditions Conditions to check.
	 * @return array<string>
	 */
	private function get_license_conditions( $conditions ) {
		return array_filter(
			$conditions,
			function( $condition ) {
				return $this->is_license_level( $condition );
			}
		);
	}

	/**
	 * Returns only version-number conditions.
	 *
	 * @since 4.4.5
	 *
	 * @param array<string> $conditions Conditions to check.
	 * @return array<string>
	 */
	private function get_version_conditions( $conditions ) {
		return array_filter(
			$conditions,
			function( $condition ) {
				return $this->is_version_number( $condition );
			}
		);
	}

	/**
	 * Determines if the current license level matches the condition.
	 *
	 * @since 4.4.5
	 *
	 * @param array<string> $license_levels License level to check against current.
	 * @return bool
	 */
	public function is_valid_license_level( $license_levels ) {
		return in_array( $this->license->get_level(), $license_levels, true );
	}

	/**
	 * Determines if two version numbers match or falls within the wildcard.
	 *
	 * @since 4.4.5
	 *
	 * @param string $current_version Current version.
	 * @param string $compare_version Version to compare with. This can either be an exact version number or a
	 *                                wildcard (e.g. `2.11.3` or `2.x`). Hyphens are also accepted in lieu of
	 *                                full stops (e.g. `2-11-3` or `2-x`).
	 * @return bool
	 */
	public function is_version_number_match( $current_version, $compare_version ) {
		$current_version_pieces = explode( '.', $current_version );

		if ( false !== strpos( $compare_version, '.' ) ) {
			$compare_version_pieces = explode( '.', $compare_version );
		} else if ( false !== strpos( $compare_version, '-' ) ) {
			$compare_version_pieces = explode( '-', $compare_version );
		} else {
			return false;
		}

		$number_current_version_parts = count( $current_version_pieces );
		$number_compare_version_parts = count( $compare_version_pieces );

		/*
		 * Normalize the two parts so that they have the same lengths and
		 * wildcards (`x`) are removed.
		 */
		for ( $i = 0; $i < $number_current_version_parts || $i < $number_compare_version_parts; $i ++ ) {
			if (
				isset( $compare_version_pieces[ $i ] ) &&
				'x' === strtolower( $compare_version_pieces[ $i ] )
			) {
				unset( $compare_version_pieces[ $i ] );
			}

			if ( ! isset( $current_version_pieces[ $i ] ) ) {
				unset( $compare_version_pieces[ $i ] );
			} elseif ( ! isset( $compare_version_pieces[ $i ] ) ) {
				unset( $current_version_pieces[ $i ] );
			}
		}

		// Now make sure all the numbers match.
		foreach ( $compare_version_pieces as $index => $versionPiece ) {
			if (
				! isset( $current_version_pieces[ $index ] ) ||
				$current_version_pieces[ $index ] !== $versionPiece
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns alid license levels for license level conditions.
	 *
	 * @todo Pull from a source of truth.
	 *
	 * @since 4.4.5
	 * @return array<string>
	 */
	private function get_valid_license_levels() {
		return array(
			'lite',
			'personal',
			'plus',
			'professional',
			'ultimate',
			'elite',
		);
	}

	/**
	 * Determines if a condition is a license level.
	 *
	 * @since 4.4.5
	 *
	 * @param string $condition Condition to check if it is a license level.
	 * @return bool
	 */
	private function is_license_level( $condition ) {
		return false !== array_search(
			$condition,
			$this->get_valid_license_levels(),
			true
		);
	}

	/**
	 * Determines if a condition is a version number.
	 *
	 * @since 4.4.5
	 *
	 * @param string $condition Condition to check if it is a version number.
	 * @return bool
	 */
	private function is_version_number( $condition ) {
		// First character should always be numeric.
		if ( ! is_numeric( substr( $condition, 0, 1 ) ) ) {
			return false;
		}

		// Must contain at least one `.` or `-`.
		return (
			false !== strpos( $condition, '.' ) ||
			false !== strpos( $condition, '-' )
		);
	}

}