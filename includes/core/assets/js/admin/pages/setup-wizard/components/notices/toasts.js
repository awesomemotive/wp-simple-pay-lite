/**
 * External dependencies
 */
import { filter } from 'lodash';

/**
 * WordPress dependencies
 */
import { useSelect, useDispatch } from '@wordpress/data';
import { SnackbarList } from '@wordpress/components';

export function Toasts( props ) {
	const notices = useSelect(
		( select ) =>
			filter( select( 'core/notices' ).getNotices(), {
				type: 'snackbar',
			} ),
		[]
	);

	const { removeNotice } = useDispatch( 'core/notices' );

	if ( 0 === notices.length ) {
		return null;
	}

	return (
		<SnackbarList
			onRemove={ removeNotice }
			notices={ notices }
			{ ...props }
		/>
	);
}
