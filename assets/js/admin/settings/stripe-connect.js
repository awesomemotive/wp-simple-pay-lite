/* global wpspHooks */

/**
 * Toggle fields based on current mode.
 */
export default function toggleStripeConnectNotice( newMode, oldMode ) {
	// Only how a notice when the mode changes.
	if ( newMode === oldMode ) {
		return;
	}

	const notice = document.getElementById( 'simpay-test-mode-toggle-notice' );
	const statusText = document.getElementById( 'simpay-toggle-notice-status' );
	const statusLink = document.getElementById( 'simpay-toggle-notice-status-link' );

	statusText.innerHTML = '<strong>' + statusText.dataset[ newMode ] + '</strong>';
	statusLink.href = statusLink.dataset[ newMode ];

	notice.classList.add( 'notice' );
	notice.classList.add( 'notice-warning' );
	notice.style.display = 'block';
}
