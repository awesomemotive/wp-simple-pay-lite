/* global accounting */

/**
 * WordPress dependencies
 */
import { decodeEntities } from '@wordpress/html-entities';

export default ( { currency } ) => {
	const {
		position: currencyPosition,
		symbol: currencySymbol,
		thousand_separator: currencyThousandSeparator,
		decimal_separator: currencyDecimalSeperator,
	} = currency;

	return {
		responsive: true,
		maintainAspectRatio: false,
		interaction: {
			mode: 'index',
			intersect: false,
		},
		plugins: {
			filler: {
				propagate: false,
			},
		},
		scales: {
			xAxis: {
				distribution: 'series',
				display: 'auto',
				ticks: {
					z: -1,
					autoSkip: false,
					maxTicksLimit: 7,
					padding: 0,
					display: false,
				},
				grid: {
					z: -1,
					drawTicks: false,
				},
			},
			yAxis: {
				beginAtZero: true,
				ticks: {
					z: -1,
					type: 'linear',
					maxTicksLimit: 6,
					align: 'center',
					lineWidth: 0,
					callback( value ) {
						const symbol = decodeEntities( currencySymbol );
						let format;

						switch ( currencyPosition ) {
							case 'left_space':
								format = '%s %v';
								break;
							case 'right':
								format = '%v%s';
								break;
							case 'right_space':
								format = '%v %s';
								break;
							default:
								format = '%s%v';
						}

						return accounting.formatMoney( value, {
							symbol,
							format,
							precision: 0,
							decimal: currencyDecimalSeperator,
							thousand: currencyThousandSeparator,
						} );
					},
				},
				grid: {
					z: -1,
					display: false,
					drawBorder: false,
				},
			},
		},
		legend: {
			display: false,
		},
		responsiveAnimationDuration: 0,
	};
};
