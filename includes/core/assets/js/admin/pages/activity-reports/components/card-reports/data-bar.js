/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

const baseClassName = 'simpay-activity-reports-data-bar';

function DataBar( { isLoading, label, data } ) {
	return (
		<div className={ baseClassName }>
			<div className={ `${ baseClassName }__header` }>
				<h3 className={ `${ baseClassName }__label` }>{ label }</h3>

				{ ! isLoading && (
					<div className={ `${ baseClassName }__legend` }>
						{ data.map(
							( { label: legendLabel, color: legendColor } ) => (
								<div
									key={ legendLabel }
									className={ `${ baseClassName }__legend-item` }
									style={ { color: legendColor } }
								>
									<span>{ legendLabel }</span>
								</div>
							)
						) }
					</div>
				) }
			</div>

			<div className={ `${ baseClassName }__bar` }>
				{ ! isLoading &&
					data.length > 0 &&
					data.map( ( dataItem ) => {
						return (
							<Button
								key={ dataItem.label }
								label={ `${ dataItem.label }: ${ dataItem.value }%` }
								showTooltip={ true }
								className={ `${ baseClassName }__bar-item` }
								style={ {
									backgroundColor: dataItem.color,
									flexBasis: `${ dataItem.value }%`,
									flexGrow: 1,
								} }
							>
								<div className="screen-reader-text">
									{ dataItem.label }
								</div>
							</Button>
						);
					} ) }

				{ ! isLoading && data.length === 0 && (
					<div className={ `${ baseClassName }__bar-item-none` }>
						{ __( 'n/a', 'simple-pay' ) }
					</div>
				) }
			</div>
		</div>
	);
}

export default DataBar;
