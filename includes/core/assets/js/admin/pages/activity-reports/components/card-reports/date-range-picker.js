/**
 * External dependencies
 */
import { differenceInMonths, format } from 'date-fns';

/**
 * WordPress dependencies
 */
import {
	Button,
	DatePicker,
	Dropdown,
	SelectControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	getSettings,
	__experimentalGetSettings as experimentalGetSettings,
	dateI18n,
} from '@wordpress/date';

/**
 * Internal dependencies
 */
import { getEndDateFromType, getStartDateFromType } from '@wpsimplepay/charts';

let getDateSettings = getSettings;

// Use the experimental date settings if getDateSettings is no an available function.
if ( ! getDateSettings ) {
	getDateSettings = experimentalGetSettings;
}

const today = new Date();

function DateRangePicker( { range, setRange } ) {
	const dateSettings = getDateSettings();

	const dateRangeOptions = [
		{
			label: __( 'Today', 'simple-pay' ),
			value: 'today',
		},
		{
			label: __( 'Last 7 days', 'simple-pay' ),
			value: '7days',
		},
		{
			label: __( 'Last 4 weeks', 'simple-pay' ),
			value: '4weeks',
		},
		{
			label: __( 'Last 3 months', 'simple-pay' ),
			value: '3months',
		},
		{
			label: __( 'Last 12 months', 'simple-pay' ),
			value: '12months',
		},
		{
			label: __( 'Month to date', 'simple-pay' ),
			value: 'monthtodate',
		},
		{
			label: __( 'Year to date', 'simple-pay' ),
			value: 'yeartodate',
		},
	];

	function computeAndSetDateRange( type ) {
		setRange( {
			...range,
			type,
			start: getStartDateFromType( type, today ),
			end: getEndDateFromType( type, range.end ),
		} );
	}

	// If the range is larger than 1 year (showing monthly intervals) ensure
	// the start and end labels are formatted as months and years.
	let startLabel;
	let endLabel;

	if (
		differenceInMonths( new Date( range.end ), new Date( range.start ) ) >
		12
	) {
		startLabel = dateI18n( 'F Y', range.start );
		endLabel = dateI18n( 'F Y', range.end );
	} else {
		startLabel = dateI18n( dateSettings.formats.date, range.start );
		endLabel = dateI18n( dateSettings.formats.date, range.end );
	}

	return (
		<div className="simpay-activity-reports-card-reports-date-range">
			<SelectControl
				label={ __( 'Report range', 'simple-pay' ) }
				value={ range.type }
				options={
					range.type === 'custom'
						? [
								...dateRangeOptions,
								{
									label: __( 'Custom', 'simple-pay' ),
									value: 'custom',
								},
						  ]
						: dateRangeOptions
				}
				onChange={ computeAndSetDateRange }
				hideLabelFromVision={ true }
			/>

			<Dropdown
				position="bottom center"
				popoverProps={ {
					noArrow: false,
				} }
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button
						variant="tertiary"
						onClick={ onToggle }
						aria-expanded={ isOpen }
					>
						{ startLabel }
					</Button>
				) }
				renderContent={ ( { onToggle } ) => (
					<DatePicker
						currentDate={ new Date( range.start ) }
						onChange={ ( date ) => {
							setRange( {
								...range,
								start: format(
									new Date( date ),
									'yyyy-MM-dd 00:00:00'
								),
								type: 'custom',
							} );
							onToggle();
						} }
						startOfWeek={ dateSettings.l10n.startOfWeek }
						isInvalidDate={ ( date ) => {
							return date > new Date( range.end );
						} }
					/>
				) }
			/>

			<span style={ { color: '#ccc' } }>&ndash;</span>

			<Dropdown
				position="bottom center"
				popoverProps={ {
					noArrow: false,
				} }
				renderToggle={ ( { isOpen, onToggle } ) => (
					<Button
						variant="tertiary"
						onClick={ onToggle }
						aria-expanded={ isOpen }
					>
						{ endLabel }
					</Button>
				) }
				renderContent={ ( { onToggle } ) => (
					<DatePicker
						currentDate={ new Date( range.end ) }
						onChange={ ( date ) => {
							setRange( {
								...range,
								end: format(
									new Date( date ),
									'yyyy-MM-dd 23:59:59'
								),
								type: 'custom',
							} );
							onToggle();
						} }
						startOfWeek={ dateSettings.l10n.startOfWeek }
						isInvalidDate={ ( date ) => {
							return (
								date < new Date( range.start ) ||
								date > new Date()
							);
						} }
					/>
				) }
			/>
		</div>
	);
}

export default DateRangePicker;
