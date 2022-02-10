/**
 * External dependencies
 */
import styled from '@emotion/styled';

/**
 * WordPress dependencies
 */
import { Card, CardHeader, CardFooter, CardBody } from '@wordpress/components';

export const Container = styled( Card )`
	padding: 30px;

	&& {
		box-shadow: 0 2px 6px 0 rgba( 0, 0, 0, 0.05 );
		border: 1px solid #ccc;
		width: 100%;
	}
`;

export const Header = styled( CardHeader )`
	&&& {
		padding: 0 0 30px;
	}

	h1 {
		font-size: 26px;
		line-height: 1;
		margin: 0;
		padding: 0;
	}

	small {
		color: #c0c0c0;
		font-size: 13px;
		font-weight: normal;
		display: block;
		margin: 0 0 3px;
	}
`;

export const Footer = styled( CardFooter )`
	&&& {
		padding: 30px 0 0;
	}
`;

export const Body = styled( CardBody )`
	&&& {
		padding: 30px 0;
	}

	> * {
		margin: 0 0 2rem;

		&:last-child {
			margin-bottom: 0;
		}
	}

	> p {
		color: #555555;
		font-size: 15px;
	}
`;
