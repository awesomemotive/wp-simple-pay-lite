/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useEffect, useReducer } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'RECEIVE':
			return {
				...state,
				data: action.data,
				error: null,
			};
		case 'ERROR':
			return {
				...state,
				error: action.error,
				data: false,
			};
		case 'START_RESOLUTION':
			return {
				...state,
				isLoading: true,
				error: null,
			};
		case 'FINISH_RESOLUTION':
			return {
				...state,
				isLoading: false,
			};
		default:
			throw new Error();
	}
};

function useRestApiReport( restApiPath, args, deps ) {
	const [ report, dispatchReport ] = useReducer( reducer, {
		data: false,
		isLoading: true,
		error: null,
	} );

	/**
	 * Fetch API data when the currency or range changes.
	 */
	useEffect( () => {
		const path = addQueryArgs( restApiPath, args );

		dispatchReport( {
			type: 'START_RESOLUTION',
		} );

		apiFetch( {
			path,
		} )
			.then( ( reportData ) => {
				dispatchReport( {
					type: 'RECEIVE',
					data: reportData,
				} );
			} )
			.catch( ( error ) => {
				dispatchReport( {
					type: 'ERROR',
					error,
				} );
			} )
			.finally( () => {
				dispatchReport( {
					type: 'FINISH_RESOLUTION',
				} );
			} );
	}, deps );

	return report;
}

export default useRestApiReport;
