/* global jQuery, wpApiSettings */

/**
 * Make an API request to the REST API.
 *
 * @param {String} route REST route.
 * @param {Object} data Data to send to the request.
 * @return {Promise} jQuery promise.
 */
export function apiRequest( route, data ) {
	return jQuery.ajax( {
		data,
		method: 'POST',
		url: `${ wpApiSettings.root }wpsp/${ route }`,
		beforeSend: ( xhr ) => {
			xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
		},
	} );
}