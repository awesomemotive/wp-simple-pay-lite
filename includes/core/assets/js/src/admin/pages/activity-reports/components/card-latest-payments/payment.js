/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';

function Payment( payment ) {
	const {
		currency,
		amount_total_formatted: amount,
		status,
		status_formatted: statusFormatted,
		email,
		payment_method_type_icon: paymentMethodTypeIcon,
		date_created_human_time_diff: ago,
		links: { customer: customerLink, payment: paymentLink },
	} = payment;
	const rowClassName = 'simpay-activity-reports-card-latest-payment';
	const badgeClassNames = classnames( 'simpay-activity-reports-badge', {
		'is-succeeded': 'succeeded' === status,
		'is-failed': 'failed' === status,
	} );

	return (
		<tr className={ rowClassName }>
			<td className={ `${ rowClassName }__amount` }>
				{ decodeEntities( amount ) }{ ' ' }
				<small style={ { fontWeight: 'normal' } }>
					{ currency.toUpperCase() }
				</small>
			</td>
			<td className={ `${ rowClassName }__type` }>
				{ '' !== paymentMethodTypeIcon && (
					<span
						dangerouslySetInnerHTML={ {
							__html: paymentMethodTypeIcon,
						} }
					/>
				) }
			</td>
			<td className={ `${ rowClassName }__status` }>
				<div className={ badgeClassNames }>
					<span>{ statusFormatted }</span>

					<svg
						aria-hidden="true"
						className="simpay-activity-reports-badge__icon"
						height="12"
						width="12"
						viewBox="0 0 16 16"
						xmlns="http://www.w3.org/2000/svg"
						fill="currentColor"
						style={ { marginLeft: '4px' } }
					>
						{ 'succeeded' === status ? (
							<path
								d="M5.297 13.213.293 8.255c-.39-.394-.39-1.033 0-1.426s1.024-.394 1.414 0l4.294 4.224 8.288-8.258c.39-.393 1.024-.393 1.414 0s.39 1.033 0 1.426L6.7 13.208a.994.994 0 0 1-1.402.005z"
								fillRule="evenodd"
							/>
						) : (
							<path
								d="m8 6.585 4.593-4.592a1 1 0 0 1 1.415 1.416L9.417 8l4.591 4.591a1 1 0 0 1-1.415 1.416L8 9.415l-4.592 4.592a1 1 0 0 1-1.416-1.416L6.584 8l-4.59-4.591a1 1 0 1 1 1.415-1.416z"
								fillRule="evenodd"
							/>
						) }
					</svg>
				</div>
			</td>
			<td className={ `${ rowClassName }__email` }>
				<Tooltip text={ __( 'View customer records', 'simple-pay' ) }>
					<a
						href={ customerLink }
						target="_blank"
						rel="noreferrer noopener"
					>
						{ email }
					</a>
				</Tooltip>
			</td>
			<td className={ `${ rowClassName }__date` }>
				<Tooltip text={ __( 'View payment record', 'simple-pay' ) }>
					<a
						href={ paymentLink }
						target="_blank"
						rel="noreferrer noopener"
					>
						{ ago }
					</a>
				</Tooltip>
			</td>
		</tr>
	);
}

export default Payment;
