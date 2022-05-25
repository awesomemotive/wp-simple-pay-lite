/**
 * WordPress dependencies
 */
import { addQueryArgs, getAuthority } from '@wordpress/url';

/**
 * Determines if a URL is external.
 *
 * @param {string} url URL to check.
 * @return {boolean} Whether the URL is external.
 */
export function isExternalUrl( url ) {
	return getAuthority( window.location.href ) !== getAuthority( url );
}

/**
 * Creates a URL with UTM parameters.
 *
 * @param {string} url Base URL
 * @param {string} utmMedium utm_medium parameter.
 * @param {string} utmContent utm_content parmaeter
 * @param {Bool} isLite Lite or Pro.
 * @return {string} URL with utm_medium and utm_content parameters.
 */
export function getGaUrl( url, utmMedium, utmContent, isLite ) {
	if ( false === isExternalUrl( url ) ) {
		return url;
	}

	return addQueryArgs( url.replace( /\/?$/, '/' ), {
		utm_source: 'WordPress',
		utm_campaign: isLite ? 'lite-plugin' : 'pro-plugin',
		utm_medium: utmMedium,
		utm_content: utmContent,
	} );
}
