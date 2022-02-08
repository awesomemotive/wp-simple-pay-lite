/**
 * External dependencies
 */
import styled from '@emotion/styled';

export const UpgradeCta = styled.div`
	text-align: center;

	h4 {
		color: #428bca;
		font-size: 18px;
		margin: 0;
	}

	h3 {
		font-size: 22px;
		margin: 15px 0 30px;
	}

	.components-button {
		font-size: 16px;
		font-weight: bold;
		padding: 15px 30px !important;
		height: auto;
	}
`;

export const UpgradeCtaFeatures = styled.ul`
	&& {
		display: flex;
		flex-wrap: wrap;
		max-width: 80%;
		margin: 30px auto;
	}

	li {
		width: 50%;
	}
`;
