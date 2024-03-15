/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const Container = styled.div`
	max-width: 80%;
	display: flex;
	align-items: center;

	> * {
		margin-right: 8px;

		&:last-child {
			margin-right: 0;
		}
	}
`;

const indicatorColors = {
	current: 'var(--wp-admin-theme-color)',
	complete: 'var(--wp-admin-theme-color)',
	incomplete: '#c0c0c0',
};

export const StepIndicator = styled.div`
	width: 16px;
	height: 16px;
	border-radius: 50%;
	background-color: ${ ( props ) => indicatorColors[ props.status ] };
	flex-shrink: 0;
	position: relative;
	transition: backgroundColor 0.2s ease-in-out;

	svg {
		fill: #fff;
		position: absolute;
		left: -1px;
		top: -1px;
		transition: opacity 0.2s ease-in-out;
		opacity: ${ ( { isComplete } ) => ( isComplete ? '1' : '0' ) };
	}

	div {
		opacity: ${ ( { isCurrent, isComplete } ) =>
			isCurrent && ! isComplete ? '1' : '0' };
	}

	path {
		stroke: #fff;
	}
`;

export const StepIndicatorCurrent = styled.div`
	width: 6px;
	height: 6px;
	background-color: #f0f0f1;
	border-radius: 50%;
	position: absolute;
	top: 5px;
	left: 5px;
	transition: opacity 0.2s ease-in-out;
`;

export const Line = styled.div`
	width: 100%;
	height: 2px;
	background-color: rgba( 192, 192, 192, 0.5 );
	width: 100px;
`;
