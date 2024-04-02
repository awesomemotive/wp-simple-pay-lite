export default ( state, action ) => {
	switch ( action.type ) {
		case 'RECEIVE':
			return {
				...state,
				data: action.data,
			};
		case 'START_RESOLUTION':
			return {
				...state,
				isLoading: true,
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
