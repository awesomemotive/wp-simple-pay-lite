/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { maybeBlockCheckboxWithUpgradeModal } from '@wpsimplepay/utils';

/**
 * Promopts for upgrade if enabling Payment Pages with an incorrect license.
 *
 * @since 4.6.4
 */
function bindUpgradeModals() {
	// Inventory.
	const enableInventory = document.getElementById( '_inventory' );

	if ( enableInventory ) {
		enableInventory.addEventListener(
			'change',
			maybeBlockCheckboxWithUpgradeModal
		);
	}

	const enableInventoryIndividual = document.getElementById(
		'_inventory_behavior_individual'
	);

	if ( enableInventoryIndividual ) {
		enableInventoryIndividual.addEventListener(
			'change',
			maybeBlockCheckboxWithUpgradeModal
		);
	}

	// Schedule.
	const enableScheduleStart = document.getElementById( '_schedule_start' );

	if ( enableScheduleStart ) {
		enableScheduleStart.addEventListener(
			'change',
			maybeBlockCheckboxWithUpgradeModal
		);
	}

	const enableScheduleEnd = document.getElementById( '_schedule_end' );

	if ( enableScheduleEnd ) {
		enableScheduleEnd.addEventListener(
			'change',
			maybeBlockCheckboxWithUpgradeModal
		);
	}
}

domReady( bindUpgradeModals );
