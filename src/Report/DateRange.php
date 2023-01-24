<?php
/**
 * Report: DateRange
 *
 * @package SimplePay
 * @subpackage Core
 * @copyright Copyright (c) 2023, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 4.6.7
 */

namespace SimplePay\Core\Report;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * DateRange class.
 *
 * Standardizes the date range for reports.
 *
 * @since 4.6.7
 */
class DateRange {

	// Valid range types.
	const RANGE_TYPES = array(
		'today',
		'7days',
		'4weeks',
		'3months',
		'12months',
		'monthtodate',
		'yeartodate',
		'custom',
	);

	/**
	 * The type of date range.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * The start of the date range.
	 *
	 * @since 4.6.7
	 *
	 * @var \DateTimeImmutable
	 */
	public $start;

	/**
	 * The end of the date range.
	 *
	 * @since 4.6.7
	 *
	 * @var \DateTimeImmutable
	 */
	public $end;

	/**
	 * DateRange.
	 *
	 * @since 4.6.7
	 *
	 * @param string $type The type of date range.
	 * @param string $start The start of the date range.
	 * @param string $end The end of the date range.
	 * @throws \InvalidArgumentException If the date range range is invalid.
	 * @throws \InvalidArgumentException If the date range start is invalid.
	 * @throws \InvalidArgumentException If the date range end is invalid.
	 */
	public function __construct( $type, $start, $end ) {
		if ( ! in_array( $type, self::RANGE_TYPES, true ) ) {
			throw new InvalidArgumentException( 'Invalid date range type.' );
		}

		$start = new DateTimeImmutable( $start );

		if ( ! $start instanceof DateTimeImmutable ) {
			throw new InvalidArgumentException( 'Invalid date range start.' );
		}

		$end = new DateTimeImmutable( $end );

		if ( ! $end instanceof DateTimeImmutable ) {
			throw new InvalidArgumentException( 'Invalid date range end.' );
		}

		$this->type  = $type;
		$this->start = $start;
		$this->end   = $end;
	}

}
