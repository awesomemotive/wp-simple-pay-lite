/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Popover, ToggleControl } from '@wordpress/components';

export const AnalyticsOptIn = styled.div``;

export const AnalyticsLabel = styled.div`
	font-size: 15px;
	margin-bottom: 8px;
	display: flex;
	align-items: center;

	> div {
		margin-left: 8px;
	}

	svg {
		display: block;

		&:hover {
			cursor: pointer;
		}
	}
`;

export const AnalyticsFaq = styled( Popover )`
	.components-popover__content {
		padding: 20px;
		width: 200px;
	}
`;

export const AnalyticsToggle = styled( ToggleControl )`
	&& {
		.components-base-control__field {
			margin-bottom: 0;
		}
	}
`;
