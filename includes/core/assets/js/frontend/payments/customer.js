/**
 * External dependencies.
 */
import serialize from 'form-serialize';

/**
 * Internal dependencies.
 */
import { apiRequest } from '@wpsimplepay/utils';

/**
 * Create a Customer object based on current formData.
 *
 * @param {Object} data Data to pass to REST endpoint.
 * @param {jQuery} spFormElem Form element jQuery object.
 * @param {Object} formData Configured form data.
 * @return {Promise} AJAX promise.
 */
export function create( data = {}, spFormElem, formData ) {
	return apiRequest( 'v2/customer', {
		form_values: serialize( spFormElem[ 0 ], { hash: true } ),
		form_data: formData,
		form_id: formData.formId,
		...data,
	} );
}
