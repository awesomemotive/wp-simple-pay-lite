/**
 * WordPress dependencies
 */
import { forwardRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Button } from './styles.js';

export const ContinueButton = forwardRef( ( props, ref ) => {
	return <Button isPrimary variant="primary" { ...props } ref={ ref } />;
} );
