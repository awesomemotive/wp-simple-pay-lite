/* global simpayAdmin, jQuery, $ */

/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';

/**
 * Internal dependencies
 */
import { doAction } from '@wpsimplepay/hooks';
import {
	maybeBlockButtonWithUpgradeModal,
	upgradeModal,
} from '@wpsimplepay/utils';
import {
	allowMultipleLineItems,
	toggleOptionalRecurringLabel,
} from './payment';

/**
 * Shows or hides the "Price Select" field based on the current settings.
 *
 * @since 4.11.0
 */
export function togglePriceSelectField() {
	const priceSelectFieldEl = document.querySelector(
		'.simpay-custom-field-plan-select'
	);

	const priceListEl = document.getElementById( 'simpay-prices' );

	if ( ! priceSelectFieldEl || ! priceListEl ) {
		return;
	}

	const priceListCount = priceListEl.querySelectorAll( '.simpay-price' )
		.length;

	const allowMultipleLineItemsInputEl = document.querySelector(
		'#_allow_purchasing_multiple_line_items'
	);

	if (
		allowMultipleLineItemsInputEl &&
		allowMultipleLineItemsInputEl.checked
	) {
		const optionalRecurringInputEls = document.querySelectorAll(
			'input.simpay-price-enable-optional-subscription[type="checkbox"]'
		);

		const quantityToggleInputEls = document.querySelectorAll(
			'.simpay-quantity-toggle input.simpay-price-quantity[type="checkbox"]'
		);

		const customAmountInputEls = document.querySelectorAll(
			'.simpay-price-enable-custom-amount'
		);

		// If any of the inputs are checked, show the price select field.
		// Otherwise hide it when there is only one price option.
		if (
			[
				...optionalRecurringInputEls,
				...quantityToggleInputEls,
				...customAmountInputEls,
			].some( ( inputEl ) => inputEl.checked )
		) {
			priceSelectFieldEl.style.display = 'block';
		} else {
			priceSelectFieldEl.style.display =
				priceListCount > 1 ? 'block' : 'none';
		}
	} else {
		priceSelectFieldEl.style.display =
			priceListCount > 1 ? 'block' : 'none';
	}
}

/**
 * Toggles price option label display if there is more than one price option.
 *
 * @since 4.4.7
 */
function togglePriceOptionSingle() {
	const priceListEl = document.getElementById( 'simpay-prices' );

	if ( ! priceListEl ) {
		return;
	}

	const priceListCount = priceListEl.querySelectorAll( '.simpay-price' )
		.length;

	// Label.
	document
		.querySelectorAll( '.simpay-price-label-default' )
		.forEach(
			( labelEl ) =>
				( labelEl.style.display =
					priceListCount > 1 ? 'none' : 'block' )
		);

	document
		.querySelectorAll( '.simpay-price-label-display' )
		.forEach(
			( labelEl ) =>
				( labelEl.style.display =
					priceListCount > 1 ? 'block' : 'none' )
		);

	// Label field.
	document
		.querySelectorAll( '.simpay-price-option-label' )
		.forEach( ( labelEl ) => {
			// Check if more than one price options.
			if ( priceListCount > 1 ) {
				labelEl.style.display = 'block';
			} else {
				// If only one price option is available,
				// we also need to check if the value is present.
				// If not, hide the field; otherwise, show the field.
				const lavelInputEl = labelEl.querySelector( 'input' );
				// Check if lavelInputEl is not null and its value is not empty
				if ( lavelInputEl && lavelInputEl.value.trim() !== '' ) {
					labelEl.style.display = 'block';
				} else {
					labelEl.style.display = 'none';
				}
			}
		} );

	// Actions.
	document
		.querySelectorAll( '.simpay-price-default-check' )
		.forEach(
			( labelEl ) =>
				( labelEl.style.display =
					priceListCount > 1 ? 'block' : 'none' )
		);

	togglePriceSelectField();
}

/**
 * Updates the display label as settings change.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onChangeLabel( priceEl ) {
	const labelDisplay = priceEl.querySelector( '.simpay-price-label-display' );
	const labelInput = priceEl.querySelector( '.simpay-price-label' );
	let label;

	const {
		currencyPosition,
		i18n: {
			recurringIntervals,
			recurringIntervalDisplay,
			customAmountLabel,
		},
	} = simpayAdmin;

	if ( '' !== labelInput.value ) {
		label = labelInput.value;
	} else {
		const currencyInput = priceEl.querySelector( '.simpay-price-currency' );
		const amountInput = priceEl.querySelector( '.simpay-price-amount' );
		const amountTypeInput = priceEl.querySelector(
			'.simpay-price-amount-type'
		);

		const customAmountInput = priceEl.querySelector(
			'.simpay-price-custom-amount input'
		);
		const customAmountToggle = priceEl.querySelector(
			'.simpay-price-enable-custom-amount'
		);

		const currencySymbol =
			currencyInput.options[ currencyInput.selectedIndex ].dataset.symbol;

		if ( true === customAmountToggle.checked ) {
			label = customAmountInput.value;
		} else {
			label = amountInput.value;
		}

		switch ( currencyPosition ) {
			case 'left':
				label = `${ currencySymbol }${ label }`;
				break;
			case 'left_space':
				label = `${ currencySymbol } ${ label }`;
				break;
			case 'right':
				label = `${ label }${ currencySymbol }`;
				break;
			case 'right_space':
				label = `${ label } ${ currencySymbol }`;
				break;
		}

		if ( true === customAmountToggle.checked ) {
			label = customAmountLabel.replace( '%s', label );
		}

		if ( 'recurring' === amountTypeInput.value ) {
			const recurringInterval = priceEl.querySelector(
				'.simpay-price-recurring-interval'
			);

			const recurringIntervalCount = priceEl.querySelector(
				'.simpay-price-recurring-interval-count'
			);

			if ( ! recurringInterval || ! recurringIntervalCount ) {
				return;
			}

			const recurringIntervalDisplayNouns =
				recurringIntervals[
					recurringInterval.options[ recurringInterval.selectedIndex ]
						.value
				];

			let recurringIntervalDisplayReplaced = recurringIntervalDisplay;

			recurringIntervalDisplayReplaced = recurringIntervalDisplayReplaced.replace(
				'%1$s',
				label
			);

			recurringIntervalDisplayReplaced = recurringIntervalDisplayReplaced.replace(
				'%2$s',
				recurringIntervalCount.value
			);

			recurringIntervalDisplayReplaced = recurringIntervalDisplayReplaced.replace(
				'%3$s',
				recurringIntervalCount.value === '1'
					? recurringIntervalDisplayNouns[ 0 ]
					: recurringIntervalDisplayNouns[ 1 ]
			);

			label = recurringIntervalDisplayReplaced;
		}
	}

	labelDisplay.innerHTML = label;

	doAction( 'simpayFormBuilderPriceOptionLabelUpdated', label, priceEl );
}

/**
 * Handles changing the current price option's currency.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onChangeCurrency( priceEl ) {
	const { options, selectedIndex } = priceEl.querySelector(
		'.simpay-price-currency'
	);
	const { symbol } = options[ selectedIndex ].dataset;

	const currenySymbolEls = priceEl.querySelectorAll(
		'.simpay-price-currency-symbol'
	);

	currenySymbolEls.forEach(
		( currencySymbolEl ) => ( currencySymbolEl.innerText = symbol )
	);
}

/**
 * Changes the status of the recurring toggle based on the amount type.
 *
 * @param {HTMLElement} priceEl Price container element.
 * @param {Object} toggle Price type toggle element.
 */
function changeRecurringToggleStatus( priceEl, toggle ) {
	const { amountType } = toggle.dataset;

	// if amount type is recurring the check the @canRecurToggle and make it disable otherwise make it enable.
	// also change the label text to 'Automatically activate a recurring subscription' if amount type is recurring.
	// otherwise change the label text to 'Allow price to optionally be purchased as a subscription'.
	const canRecurLabel = priceEl.querySelector(
		'.simpay-price-can-recur-label'
	);
	const canRecurToggle = priceEl.querySelector(
		'.simpay-price-enable-optional-subscription'
	);
	const falseRecurToggle = priceEl.querySelector(
		'.simpay-false-recurring-checkbox'
	);
	if ( 'recurring' === amountType ) {
		let recurringText = simpayAdmin.i18n.priceRecurring;
		if ( canRecurToggle.checked ) {
			recurringText = simpayAdmin.i18n.priceOptionalRecurring;
		}
		canRecurLabel.innerHTML = recurringText;
		canRecurToggle.style.display = 'none';
		falseRecurToggle.style.display = '';
		canRecurToggle.checked = false;
	} else {
		canRecurLabel.innerHTML = simpayAdmin.i18n.priceOptionalRecurring;
		canRecurToggle.style.display = '';
		falseRecurToggle.style.display = 'none';
	}
}

/**
 * Handles displaying the current price's relevant settings when a Price's
 * "Amount Type" changes.
 *
 * @param {HTMLElement} priceEl Price container element.
 * @param {HTMLElement} toggle Price type toggle element.
 */
function onToggleAmountType( priceEl, toggle ) {
	// Disable current toggles.
	const toggles = priceEl.querySelectorAll(
		'.simpay-price-amount-type .button'
	);

	toggles.forEach( ( toggleEl ) =>
		toggleEl.classList.remove( 'button-primary' )
	);

	// Update current toggle and show relevant settings.
	const { amountType } = toggle.dataset;

	toggle.classList.add( 'button-primary' );

	// Hide "optional recur" setting.
	changeRecurringToggleStatus( priceEl, toggle );

	toggleOptionalRecurringLabel( priceEl )

	// Update the hidden field to track the amount type.
	priceEl.querySelector( '.simpay-price-amount-type' ).value = amountType;
}

/**
 * Handles validating the recurring interval. Can not be over one year.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onChangeRecurring( priceEl ) {
	const recurringInterval = priceEl.querySelector(
		'.simpay-price-recurring-interval'
	);

	const recurringIntervalCount = priceEl.querySelector(
		'.simpay-price-recurring-interval-count'
	);

	if ( ! recurringInterval || ! recurringIntervalCount ) {
		return;
	}

	const recurringIntervalCountValue = parseInt(
		recurringIntervalCount.value
	);

	const recurringIntervalValue =
		recurringInterval.options[ recurringInterval.selectedIndex ].value;

	// Limit each interval to maximum 1 year (imposed by Stripe).
	switch ( recurringIntervalValue ) {
		case 'day':
			if ( recurringIntervalCountValue > 365 ) {
				recurringIntervalCount.value = 365;
			}
			break;
		case 'week':
			if ( recurringIntervalCountValue > 52 ) {
				recurringIntervalCount.value = 52;
			}
			break;
		case 'month':
			if ( recurringIntervalCountValue > 12 ) {
				recurringIntervalCount.value = 12;
			}
			break;
		case 'year':
			if ( recurringIntervalCountValue > 1 ) {
				recurringIntervalCount.value = 1;
			}
			break;
	}

	// Update the recurring interval pluralization based on the count value.
	const pluralizations = JSON.parse( recurringInterval.dataset.intervals );

	[ ...recurringInterval.options ].forEach( ( { value }, i ) => {
		recurringInterval.options[ i ].text =
			parseInt( recurringIntervalCount.value ) === 1
				? pluralizations[ value ][ 0 ]
				: pluralizations[ value ][ 1 ];
	} );
}

/**
 * Handles displaying the current price's legacy settings.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onToggleLegacySettings( priceEl ) {
	const legacySettingEls = priceEl.querySelectorAll(
		'.simpay-price-legacy-setting'
	);

	legacySettingEls.forEach(
		( legacySettingEl ) =>
			( legacySettingEl.style.display =
				'block' === legacySettingEl.style.display ? 'none' : 'block' )
	);
}

/**
 * Handles toggling the current price option as the default selection.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onToggleDefault( priceEl ) {
	const allDefaults = document.querySelectorAll( '.simpay-price-default' );

	allDefaults.forEach( ( defaultEl ) => ( defaultEl.checked = false ) );

	priceEl.querySelector( '.simpay-price-default' ).checked = true;
}

/**
 * Handles remove a price.
 *
 * @param {HTMLElement} priceEl Price container element.
 */
function onRemove( priceEl ) {
	priceEl.remove();
	ensureDefaultPrice();

	// Toggle label fields.
	togglePriceOptionSingle();

	doAction( 'simpayFormBuilderPriceRemoved', priceEl.id );
}

/**
 * Handles adding a new price option.
 *
 * @param {HTMLElement} buttonEl "Add Price" button.
 */
function onAddPrice( buttonEl ) {
	const { nonce, formId } = buttonEl.dataset;
	const priceListEl = document.getElementById( 'simpay-prices' );

	// Disable button.
	buttonEl.classList.add( 'disabled' );

	wp.ajax.send( 'simpay_add_price', {
		data: {
			_wpnonce: nonce,
			form_id: formId,
		},
		success: ( response ) => {
			jQuery( priceListEl ).append( response );

			// Rebind events when added.
			bindPriceOptions();
			ensureDefaultPrice();

			// Reenable button.
			buttonEl.classList.remove( 'disabled' );

			// Toggle label fields.
			togglePriceOptionSingle();

			// Toggle quantity field.
			allowMultipleLineItems();

			doAction( 'simpayFormBuilderPriceAdded', response );
		},
		error: ( { message } ) => {
			window.alert( message );

			// Reenable button.
			buttonEl.classList.remove( 'disabled' );
		},
	} );
}

/**
 * Handles adding an existing (legacy) Stripe Plan.
 *
 * @param {HTMLElement} buttonEl "Add Plan" button.
 */
function onAddPlan( buttonEl ) {
	const { nonce, formId } = buttonEl.dataset;
	const priceListEl = document.getElementById( 'simpay-prices' );
	const planIdEl = document.getElementById(
		'simpay-prices-advanced-plan-id'
	);

	buttonEl.classList.add( 'disabled' );

	wp.ajax.send( 'simpay_add_plan', {
		data: {
			_wpnonce: nonce,
			form_id: formId,
			plan_id: planIdEl.value,
		},
		success: ( response ) => {
			jQuery( priceListEl ).append( response );

			// Rebind events when added.
			bindPriceOptions();

			// Hide advanced settings and clear input.
			document.getElementById( 'simpay-prices-advanced' ).style.display =
				'none';
			planIdEl.value = '';

			// Reenable button.
			buttonEl.classList.remove( 'disabled' );

			// Toggle label fields.
			togglePriceOptionSingle();
		},
		error: ( { message } ) => {
			window.alert( message );

			// Reenable button.
			buttonEl.classList.remove( 'disabled' );
		},
	} );
}

/**
 * Binds jQuery sortable to price options.
 */
function bindSortablePriceOptions() {
	jQuery( '.simpay-prices' ).sortable( {
		items: '.simpay-field-metabox',
		containment: '#simpay-prices',
		handle: '.simpay-hndle',
		placeholder: 'sortable-placeholder',
		cursor: 'move',
		delay: jQuery( document.body ).hasClass( 'mobile' ) ? 200 : 0,
		distance: 2,
		tolerance: 'pointer',
		forcePlaceholderSize: true,
		opacity: 0.65,

		// @link https://core.trac.wordpress.org/changeset/35809
		helper( event, element ) {
			/* `helper: 'clone'` is equivalent to `return element.clone();`
			 * Cloning a checked radio and then inserting that clone next to the original
			 * radio unchecks the original radio (since only one of the two can be checked).
			 * We get around this by renaming the helper's inputs' name attributes so that,
			 * when the helper is inserted into the DOM for the sortable, no radios are
			 * duplicated, and no original radio gets unchecked.
			 */
			return element
				.clone()
				.find( ':input' )
				.attr( 'name', function ( i, currentName ) {
					return (
						'sort_' +
						parseInt( Math.random() * 100000, 10 ).toString() +
						'_' +
						currentName
					);
				} )
				.end();
		},
	} );
}

/**
 * Binds events to various elements on each price option.
 */
function bindPriceOptions() {
	const pricesEls = document.querySelectorAll( '.simpay-price' );

	pricesEls.forEach( ( priceEl ) => {
		// Label.
		const labelInput = priceEl.querySelector( '.simpay-price-label' );

		labelInput.addEventListener( 'keyup', () => onChangeLabel( priceEl ) );
		onChangeLabel( priceEl );

		// Currency symbol.
		const currencyToggle = priceEl.querySelector(
			'.simpay-price-currency'
		);

		currencyToggle.addEventListener( 'change', () => {
			onChangeCurrency( priceEl );
			onChangeLabel( priceEl );
		} );

		// Amount type toggle.
		const amountTypeToggles = priceEl.querySelectorAll(
			'.simpay-price-amount-type .button'
		);

		if ( amountTypeToggles.length > 0 ) {
			amountTypeToggles.forEach( ( amountTypeToggle ) => {
				if ( amountTypeToggle.classList.contains( 'button-primary' ) ) {
					changeRecurringToggleStatus( priceEl, amountTypeToggle );
				}
				amountTypeToggle.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					const {
						available,
						upgradeTitle,
						upgradeDescription,
						upgradeUrl,
						upgradePurchasedUrl,
					} = e.target.dataset;

					if ( 'no' === available ) {
						upgradeModal( {
							title: upgradeTitle,
							description: upgradeDescription,
							url: upgradeUrl,
							purchasedUrl: upgradePurchasedUrl,
						} );
					} else {
						onToggleAmountType( priceEl, e.target );
						onChangeLabel( priceEl );
					}
				} );
			} );
		}

		// Amount.
		const amountInput = priceEl.querySelector( '.simpay-price-amount' );

		amountInput.addEventListener( 'keyup', () => onChangeLabel( priceEl ) );

		// Optional recurring toggle.
		const canRecurToggle = priceEl.querySelector(
			'.simpay-price-enable-optional-subscription'
		);

		if ( canRecurToggle ) {
			canRecurToggle.addEventListener( 'click', ( e ) => {
				const { target } = e;
				const {
					available,
					upgradeTitle,
					upgradeDescription,
					upgradeUrl,
					upgradePurchasedUrl,
				} = target.dataset;

				if ( 'no' === available ) {
					target.checked = false;
					e.preventDefault();

					upgradeModal( {
						title: upgradeTitle,
						description: upgradeDescription,
						url: upgradeUrl,
						purchasedUrl: upgradePurchasedUrl,
					} );
				} else {
					onChangeLabel( priceEl );
					togglePriceSelectField();
					toggleOptionalRecurringLabel( priceEl );
				}
			} );
		}

		// Custom amount toggle.
		const customAmountToggle = priceEl.querySelector(
			'.simpay-price-enable-custom-amount'
		);

		customAmountToggle.addEventListener( 'change', () => {
			onChangeLabel( priceEl );
			togglePriceSelectField();
		} );

		// Recurring interval.
		const recurringInterval = priceEl.querySelector(
			'.simpay-price-recurring-interval'
		);

		// Recurring interval count.
		const recurringIntervalCount = priceEl.querySelector(
			'.simpay-price-recurring-interval-count'
		);

		if ( recurringInterval && recurringIntervalCount ) {
			recurringInterval.addEventListener( 'change', () => {
				onChangeLabel( priceEl );
				onChangeRecurring( priceEl );
			} );

			onChangeRecurring( priceEl );

			recurringIntervalCount.addEventListener( 'keyup', () => {
				onChangeRecurring( priceEl );
				onChangeLabel( priceEl );
			} );

			recurringIntervalCount.addEventListener( 'change', () => {
				onChangeRecurring( priceEl );
				onChangeLabel( priceEl );
			} );
		}

		// Legacy settings toggle.
		const legacySettingsToggle = priceEl.querySelector(
			'.simpay-price-legacy-setting-toggle'
		);

		if ( legacySettingsToggle ) {
			legacySettingsToggle.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				onToggleLegacySettings( priceEl );
			} );
		}

		// Default checkbox.
		const defaultToggle = priceEl.querySelector( '.simpay-price-default' );

		defaultToggle.addEventListener( 'change', () =>
			onToggleDefault( priceEl )
		);

		// Remove.
		const removeToggle = priceEl.querySelector( '.simpay-price-remove' );

		removeToggle.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			onRemove( priceEl );
		} );

		// Configure Button.
		const configureButtons = priceEl.querySelectorAll(
			'.simpay-price-configure-btn'
		);

		configureButtons.forEach( ( configureButton ) => {
			const isButtonLocked = configureButton.classList.contains(
				'simpay-price-locked'
			);
			configureButton.addEventListener( 'click', ( e ) => {
				e.preventDefault();

				if ( isButtonLocked ) {
					return;
				}

				const targetId = configureButton.getAttribute(
					'data-target-id'
				);

				const targetTitle = configureButton.getAttribute(
					'data-dialog-title'
				);

				onConfigure( targetId, targetTitle );
				$( `#${ targetId }` ).dialog( 'open' );
			} );
		} );

		// Quantity toggle.
		const quantityToggle = priceEl.querySelector(
			'.simpay-quantity-toggle input.simpay-price-quantity[type="checkbox"]'
		);

		if ( quantityToggle ) {
			quantityToggle.addEventListener( 'change', () => {
				togglePriceSelectField();
			} );
		}
	} );

	// Toggle label fields.
	togglePriceOptionSingle();
	togglePriceSelectField();
}

/**
 * Binds events to "Advanced" options.
 */
function bindAdvancedOptions() {
	const toggleAdvancedEl = document.getElementById(
		'simpay-prices-advanced-toggle'
	);

	const advancedEl = document.getElementById( 'simpay-prices-advanced' );

	if ( ! toggleAdvancedEl || ! advancedEl ) {
		return;
	}

	toggleAdvancedEl.addEventListener( 'click', function ( e ) {
		e.preventDefault();

		advancedEl.style.display =
			'block' === advancedEl.style.display ? 'none' : 'block';
	} );
}

/**
 * Binds events for adding a new Price.
 */
function addPrice() {
	const addButtonEl = document.getElementById( 'simpay-add-price' );

	if ( ! addButtonEl ) {
		return;
	}

	addButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		onAddPrice( addButtonEl );
	} );
}

/**
 * Shows an upgrade modal when a Lite user presses "Add Price".
 *
 * @since 4.4.7
 */
function addPriceLite() {
	const addPriceEl = document.getElementById( 'simpay-add-price-lite' );

	if ( ! addPriceEl ) {
		return;
	}

	addPriceEl.addEventListener( 'click', maybeBlockButtonWithUpgradeModal );
}

/**
 * Shows an upgrade modal when a Lite user presses "Subscription".
 *
 * @since 4.4.7
 */
function amountTypeLite() {
	const amountTypeEl = document.getElementById( 'simpay-amount-type-lite' );

	if ( ! amountTypeEl ) {
		return;
	}

	amountTypeEl.addEventListener( 'click', maybeBlockButtonWithUpgradeModal );
}

/**
 * Shows an upgrade modal when a Lite user checks option recurring.
 *
 * @since 4.4.7
 */
function canRecurLite() {
	const canRecurEl = document.getElementById( 'simpay-can-recur-lite' );

	if ( ! canRecurEl ) {
		return;
	}

	canRecurEl.addEventListener( 'click', maybeBlockButtonWithUpgradeModal );
}

/**
 * Shows an upgrade modal when a Lite user checks custom amount.
 *
 * @since 4.4.7
 */
function customLite() {
	const customEl = document.getElementById( 'simpay-custom-lite' );

	if ( ! customEl ) {
		return;
	}

	customEl.addEventListener( 'click', maybeBlockButtonWithUpgradeModal );
}

/**
 * Binds events for adding an existing (legacy) Stripe Plan.
 */
function addPlan() {
	const addButtonEl = document.getElementById( 'simpay-prices-advanced-add' );

	if ( ! addButtonEl ) {
		return;
	}

	addButtonEl.addEventListener( 'click', ( e ) => {
		e.preventDefault();
		onAddPlan( addButtonEl );
	} );
}

/**
 * Shows or hides "Remove" toggles based on the current amount of prices.
 */
function ensureDefaultPrice() {
	const prices = document.querySelectorAll( '.simpay-price' );

	if ( ! document.querySelector( '.simpay-price-default:checked' ) ) {
		prices[ 0 ].querySelector( '.simpay-price-default' ).checked = true;
	}
}

/**
 * Opens a jQuery UI dialog to configure the Price.
 *
 * @since 4.11.0
 * @param {string} id Price Option ID.
 * @param {string} title Price Option title.
 */
function onConfigure( id, title ) {
	const dialogEl = $( `#${ id }` );

	dialogEl.dialog( {
		title,
		position: {
			my: 'center',
			at: 'center',
			of: window,
		},
		modal: true,
		width: 500,
		resizable: false,
		draggable: false,
		appendTo: dialogEl.parent().parent(),
		open( event ) {
			$( event.target )
				.find( '.update, .simpay-tab-link' )
				.on( 'click', ( clickEvent ) => {
					clickEvent.preventDefault();

					$( this ).dialog( 'close' );
				} );
		},
		create() {
			// style fix for WordPress admin
			$( '.ui-dialog-titlebar-close' ).addClass( 'ui-button' );
		},
	} );
}

/**
 * DOM ready.
 */
domReady( () => {
	bindSortablePriceOptions();
	bindPriceOptions();
	bindAdvancedOptions();
	addPlan();
	addPrice();
	addPriceLite();
	amountTypeLite();
	canRecurLite();
	customLite();
} );
