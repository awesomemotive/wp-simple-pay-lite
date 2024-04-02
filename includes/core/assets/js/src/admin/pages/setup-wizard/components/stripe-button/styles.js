/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Button as ButtonBase } from '@wordpress/components';

export const Button = styled( ButtonBase )`
	&&&& {
		font-size: 16px;
		font-weight: bold;
		padding: 13px 30px 12px !important;
		height: auto;
		background-color: #635bff;

		&:focus {
			background-color: #635bff;
			box-shadow: inset 0 0 0 1px #fff,
				0 0 0 var( --wp-admin-border-width-focus ) #635bff;
		}

		&:hover {
			background-color: #0a2540 !important;
		}

		&:focus:hover {
			box-shadow: inset 0 0 0 1px #fff,
				0 0 0 var( --wp-admin-border-width-focus ) #0a2540;
		}

		svg {
			margin-left: 5px;
		}
	}
`;
