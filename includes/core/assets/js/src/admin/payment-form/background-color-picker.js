/* global $ */

/**
 * Binds a background color swatch picker (preset radios plus a custom color).
 *
 * Expects the markup rendered for the Payment Page and Confirmation Page
 * "Color Scheme" settings: a `.payment-page-background-color-selector` wrapper
 * holding one `.simpay-payment-page-background-color` swatch per color, where
 * the custom swatch has a radio (`{idPrefix}-custom-radio`) that is submitted
 * and a text input (`{idPrefix}-custom`) that hosts the WordPress color picker.
 *
 * Every lookup is scoped to the wrapper, because both pickers render on the
 * same payment form edit screen and share the swatch class names.
 *
 * @since 4.17.5
 *
 * @param {string} idPrefix Element ID prefix, e.g. `payment-page-background-color`.
 */
export function bindBackgroundColorPicker( idPrefix ) {
	const customColorEl = document.getElementById( `${ idPrefix }-custom` );
	const customRadioEl = document.getElementById(
		`${ idPrefix }-custom-radio`
	);

	if ( ! customColorEl || ! customRadioEl ) {
		return;
	}

	const selectorEl = customColorEl.closest(
		'.payment-page-background-color-selector'
	);

	if ( ! selectorEl ) {
		return;
	}

	const colorButtonEls = selectorEl.querySelectorAll(
		'.simpay-payment-page-background-color'
	);

	const customColorContainer = customColorEl.closest(
		'.simpay-payment-page-background-color'
	);

	// The `.wp-color-result` toggle button is injected by `wpColorPicker()`,
	// so it can only be looked up once the picker has been initialized.
	let customButtonEl = null;

	/**
	 * Syncs each swatch's `is-selected` class with the checked state of its
	 * radio input, so exactly the chosen swatch (preset or custom) is
	 * highlighted.
	 *
	 * The custom swatch's checkmark is styled on the picker's
	 * `.wp-color-result` button (a radio followed by a `<label>` is only
	 * rendered for presets), so the class is mirrored onto that button too.
	 *
	 * @since 4.17.5
	 */
	function updateColorSelection() {
		colorButtonEls.forEach( ( el ) => {
			const radioInput = el.querySelector( 'input[type="radio"]' );

			if ( radioInput && radioInput.checked ) {
				el.classList.add( 'is-selected' );
			} else {
				el.classList.remove( 'is-selected' );
			}
		} );

		if ( customButtonEl ) {
			customButtonEl.classList.toggle(
				'is-selected',
				customRadioEl.checked
			);
		}
	}

	$( customColorEl ).wpColorPicker( {
		change( event, ui ) {
			// Submit the picked color and select the custom swatch.
			customRadioEl.value = ui.color.toString();
			customRadioEl.checked = true;
			updateColorSelection();
		},
		// Defensive: WordPress fires this when the hosted text input is emptied
		// or its Clear button is clicked. The admin CSS currently hides both
		// (`.wp-picker-open + .wp-picker-input-wrap` and `.wp-picker-clear`),
		// and `data-default-color` swaps Clear for a Default button, so this
		// only runs if that markup or CSS changes. Unchecking keeps an emptied
		// picker from submitting a stale custom color.
		clear() {
			customRadioEl.checked = false;
			updateColorSelection();
		},
	} );

	// Opening the picker does not change the selection on its own: the custom
	// swatch is selected by the `change` callback once a color is picked, so
	// opening and closing it keeps the chosen preset instead of submitting
	// the placeholder color.
	customButtonEl = customColorContainer
		? customColorContainer.querySelector( '.wp-color-result' )
		: null;

	// The custom radio is included: the swatch radios stay in the keyboard
	// order, so arrowing onto it must highlight the custom swatch too.
	colorButtonEls.forEach( ( el ) => {
		const radioInput = el.querySelector( 'input[type="radio"]' );

		if ( ! radioInput ) {
			return;
		}

		radioInput.addEventListener( 'change', updateColorSelection );
	} );

	updateColorSelection();
}
