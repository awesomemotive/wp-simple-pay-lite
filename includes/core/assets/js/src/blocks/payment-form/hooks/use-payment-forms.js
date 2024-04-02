/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

const EMPTY_ARRAY = [];

/**
 * Loads payment forms from the REST API.
 *
 * @return {Object} Payment form data.
 */
export function usePaymentForms() {
	return useSelect( ( select ) => {
		const { getEntityRecords, isResolving } = select( 'core' );
		const shape = [
			'postType',
			'simple-pay',
			{
				per_page: -1,
			},
		];

		const forms = getEntityRecords( ...shape );
		const loading = isResolving( 'getEntityRecords', shape );

		return {
			paymentForms: forms || EMPTY_ARRAY,
			isLoading: loading,
			hasPaymentForms: !! forms?.length,
		};
	}, [] );
}
