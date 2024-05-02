/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Tooltip } from '@wordpress/components';
import { __ , sprintf } from '@wordpress/i18n';
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


	const getTooltipText = () => {
		if ( isPartialRefund() && 'refunded' === status ) {
			return sprintf(
				__('%s refund was initiated', 'simple-pay'),
				decodeEntities(payment.amount_refunded_formatted)
			);
		}
	};

	const isPartialRefund = () => {
		if (
			status === 'refunded' &&
			payment.amount_refunded !== payment.amount_total
		) {
			return true;
		}

		return false;
	};

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
				<Tooltip text={ getTooltipText() }>
					<div className={ badgeClassNames }>
						<span>
							{ statusFormatted }
						</span>

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
							) : 'refunded' === status && isPartialRefund() ? (
								<>
									<path
										fill-rule="evenodd"
										clip-rule="evenodd"
										d="M11 2.5H5A2.5 2.5 0 0 0 2.5 5v6A2.5 2.5 0 0 0 5 13.5h6a2.5 2.5 0 0 0 2.5-2.5V5A2.5 2.5 0 0 0 11 2.5ZM5 1a4 4 0 0 0-4 4v6a4 4 0 0 0 4 4h6a4 4 0 0 0 4-4V5a4 4 0 0 0-4-4H5Z"
									></path>
									<path
										fill-rule="evenodd"
										clip-rule="evenodd"
										d="M6.25 8A.75.75 0 0 1 7 7.25h1.25A.75.75 0 0 1 9 8v3.5a.75.75 0 0 1-1.5 0V8.75H7A.75.75 0 0 1 6.25 8Z"
									></path>
									<path d="M6.75 5a1.25 1.25 0 1 1 2.5 0 1.25 1.25 0 0 1-2.5 0Z"></path>
								</>
							) : 'refunded' === status && ! isPartialRefund() ? (
								<>
									{ /* Refund Icon Path */ }
									<path
										d="M5.994 2.38a.875.875 0 1 0-1.238-1.238l-4.25 4.25A.849.849 0 0 0 .25 6c0 .232.093.466.257.63l4.25 4.24a.875.875 0 1 0 1.236-1.24L3.238 6.875h7.387C12.492 6.875 14 8.271 14 10c0 1.797-1.578 3.375-3.375 3.375a.875.875 0 0 0 0 1.75c2.763 0 5.125-2.362 5.125-5.125 0-2.83-2.43-4.872-5.12-4.875H3.24l2.754-2.746Z"
										fillRule="evenodd"
									/>
								</>
							) : (
								<path
									d="m8 6.585 4.593-4.592a1 1 0 0 1 1.415 1.416L9.417 8l4.591 4.591a1 1 0 0 1-1.415 1.416L8 9.415l-4.592 4.592a1 1 0 0 1-1.416-1.416L6.584 8l-4.59-4.591a1 1 0 1 1 1.415-1.416z"
									fillRule="evenodd"
								/>
							) }
						</svg>
					</div>
				</Tooltip>
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
