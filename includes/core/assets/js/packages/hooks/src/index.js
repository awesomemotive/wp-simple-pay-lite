/**
 * WordPress dependencies
 */
import { createHooks } from '@wordpress/hooks';

const defaultHooks = createHooks();
const { addAction, addFilter, doAction, applyFilters } = defaultHooks;

export { addAction, addFilter, doAction, applyFilters };
export default defaultHooks;
