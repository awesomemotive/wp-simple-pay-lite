/* global simpayNotifications */

/**
 * External dependencies
 */
import moment from 'moment';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { addQueryArgs, getAuthority } from '@wordpress/url';
import { autop } from '@wordpress/autop';

const baseClassName = 'simpay-notifications-notification';
const { isLite } = simpayNotifications;

/**
 * Return a dashicon name for a notification type.
 *
 * @param {string} type Notification type.
 * @return {string} Dashicon name.
 */
function getIconName( type ) {
	switch ( type ) {
		case 'warning':
			return 'warning';
		case 'error':
			return 'dismiss';
		case 'info':
			return 'admin-generic';
		case 'success':
		default:
			return 'yes-alt';
	}
}

/**
 * Determines if a URL is external.
 *
 * @param {string} url URL to check.
 * @return {boolean} Whether the URL is external.
 */
function isExternalUrl( url ) {
	return getAuthority( window.location.href ) !== getAuthority( url );
}

/**
 * Creates a URL with UTM parameters.
 *
 * @param {string} url Base URL
 * @param {string} utmMedium utm_medium parameter.
 * @param {string} utmContent utm_content parmaeter
 * @return {string} URL with utm_medium and utm_content parameters.
 */
function getGaUrl( url, utmMedium, utmContent ) {
	if ( false === isExternalUrl( url ) ) {
		return url;
	}

	return addQueryArgs( url.replace( /\/?$/, '/' ), {
		utm_source: 'WordPress',
		utm_campaign: '1' === isLite ? 'lite-plugin' : 'pro-plugin',
		utm_medium: utmMedium,
		utm_content: utmContent,
	} );
}

function Notification( { notification, onDismissNotification } ) {
	const {
		id,
		title,
		content,
		type,
		start,
		actions,
		is_dismissible: isDismissible,
	} = notification;

	return (
		<div
			className={ baseClassName }
			style={ {
				backgroundColor: ! isDismissible ? '#fafafa' : 'transparent',
			} }
		>
			<div
				className={ `${ baseClassName }__icon ${ baseClassName }__icon-${
					type || 'success'
				}` }
			>
				<span
					className={ `dashicons dashicons-${ getIconName( type ) }` }
				/>
			</div>

			<div className={ `${ baseClassName }__body` }>
				<div className={ `${ baseClassName }__header` }>
					<div className={ `${ baseClassName }__title` }>
						{ title }
					</div>
					{ isDismissible && (
						<div className={ `${ baseClassName }__date` }>
							{ moment.unix( start ).fromNow() }
						</div>
					) }
				</div>

				<div
					className={ `${ baseClassName }__content` }
					dangerouslySetInnerHTML={ { __html: autop( content ) } }
				/>

				<div className={ `${ baseClassName }__actions` }>
					{ actions.map( ( { type: actionType, text, url } ) => (
						<Button
							key={ text }
							href={ getGaUrl(
								url,
								'notification-inbox',
								title
							) }
							isPrimary={ 'primary' === actionType }
							isSecondary={ 'secondary' === actionType }
							variant={ actionType }
							target={ isExternalUrl( url ) ? '_blank' : '_self' }
						>
							{ text }
						</Button>
					) ) }

					{ isDismissible && (
						<Button
							isLink
							variant="link"
							onClick={ () => onDismissNotification( id ) }
						>
							{ __( 'Dismiss', 'simple-pay' ) }
						</Button>
					) }
				</div>
			</div>
		</div>
	);
}

export default Notification;
