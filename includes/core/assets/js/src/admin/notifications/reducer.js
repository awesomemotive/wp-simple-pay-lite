export default ( state, action ) => {
	switch ( action.type ) {
		case 'SET':
			return {
				...state,
				data: action.notifications,
			};
		case 'DISMISS':
			return {
				...state,
				data: state.data.filter( ( item ) => item.id !== action.id ),
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
