/* global simpayHelp */

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Icon, lifesaver, postExcerpt } from '@wordpress/icons';

/**
 * Internal depenedencies
 */
import { getGaUrl } from './../../utils.js';
const { isLite } = simpayHelp;

function HelpPanelFooter( { searchTerm } ) {
	return (
		<div className="simpay-help-panel__footer">
			<div className="simpay-help-panel__footer-action">
				<Icon icon={ postExcerpt } size={ 48 } />

				<h4>{ __( 'View Documentation', 'simple-pay' ) }</h4>
				<p>
					{ __(
						'Browse documentation, references, and tutorials for WP Simple Pay.',
						'simple-pay'
					) }
				</p>

				<Button
					variant="secondary"
					isSecondary
					href={ getGaUrl(
						'https://docs.wpsimplepay.com/',
						'help',
						'' === searchTerm ? 'View Documentation' : searchTerm,
						'1' === isLite
					) }
				>
					{ __( 'View All Documentation', 'simple-pay' ) }
				</Button>
			</div>

			<div className="simpay-help-panel__footer-action">
				<Icon icon={ lifesaver } size={ 48 } />

				<h4>{ __( 'Get Support', 'simple-pay' ) }</h4>
				<p>
					{ __(
						'Submit a ticket and our world class support team will be in touch soon.',
						'simple-pay'
					) }
				</p>

				{ '1' === isLite ? (
					<Button
						className="simpay-help-panel__footer-upgrade-button"
						variant="secondary"
						isSecondary
						href={ getGaUrl(
							'https://wpsimplepay.com/lite-vs-pro/',
							'help',
							'' === searchTerm ? 'Get Support' : searchTerm,
							true
						) }
					>
						{ __( 'Upgrade to Pro', 'simple-pay' ) }
					</Button>
				) : (
					<Button
						variant="secondary"
						isSecondary
						href={ getGaUrl(
							'https://wpsimplepay.com/support',
							'help',
							'' === searchTerm ? 'Get Support' : searchTerm,
							false
						) }
					>
						{ __( 'Submit a Support Ticket', 'simple-pay' ) }
					</Button>
				) }
			</div>
		</div>
	);
}

export default HelpPanelFooter;
