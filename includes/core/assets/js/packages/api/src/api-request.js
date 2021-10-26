/* global jQuery, wpApiSettings */

/**
 * Make an API request to the REST API.
 *
 * @param {string} route REST route.
 * @param {Object} data Data to send to the request.
 * @return {jqXHR} jQuery XMLHttpRequest object.
 */
export async function apiRequest( route, data ) {
	try {
		return await jQuery.ajax( {
			data,
			method: 'POST',
			url: `${ wpApiSettings.root }wpsp/${ route }`,
			beforeSend: ( xhr ) => {
				xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
			},
		} );
	} catch ( { responseJSON } ) {
		throw responseJSON;
	}
}
