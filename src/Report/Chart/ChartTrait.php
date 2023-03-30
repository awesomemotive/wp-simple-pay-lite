<?php
/**
 * Report: Chart
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Report\Chart;

/**
 * ChartTrait trait.
 *
 * @since 4.6.7
 */
trait ChartTrait {

	/**
	 * Calculates a dataset's "total" amount.
	 *
	 * @since 4.6.7
	 *
	 * @param array<int, array<string, mixed>> $dataset The formatted dataset.
	 * @return int
	 */
	private function get_chart_formatted_dataset_total( $dataset ) {
		return array_reduce(
			$dataset,
			function( $total, $datapoint ) {
				/** @var array<string, mixed> $datapoint */
				/** @var int $total */
				/** @var int $y */
				$y = $datapoint['y'];
				return $total + $y;
			},
			0
		);
	}

	/**
	 * Returns the primary color for the current user's admin color scheme.
	 *
	 * @since 4.7.3
	 *
	 * @return array<int> RGB color values.
	 */
	protected function get_user_color_scheme_pref_primary_color() {
		$color_scheme = get_user_meta(
			get_current_user_id(),
			'admin_color',
			true
		);

		switch ( $color_scheme ) {
			case 'modern':
				return array( 56, 88, 233 );
			case 'light':
			case 'blue':
				return array( 9, 100, 132 );
			case 'coffee':
				return array( 199, 165, 137 );
			case 'ectoplasm':
				return array( 163, 183, 69 );
			case 'midnight':
			case 'ocean':
				return array( 105, 168, 187 );
			case 'sunrise':
				return array( 207, 73, 68 );
			default:
				return array( 66, 138, 202 );
		}
	}

}
