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
				return (int) $total + $y;
			},
			0
		);
	}

}
