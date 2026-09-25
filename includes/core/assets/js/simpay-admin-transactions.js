/**
 * WP Simple Pay - Admin Transactions Refund Modal
 *
 * @package SimplePay
 * @since 4.17.4
 */

( function() {
	'use strict';

	var config = window.simpayAdminTransactions || {};

	var modal        = document.getElementById( 'simpay-refund-modal' );
	var openBtn      = document.getElementById( 'simpay-open-refund-modal' );
	var closeBtn     = modal ? modal.querySelector( '.simpay-refund-modal-close' ) : null;
	var cancelBtn    = document.getElementById( 'simpay-refund-cancel' );
	var submitBtn    = document.getElementById( 'simpay-refund-submit' );
	var backdrop     = modal ? modal.querySelector( '.simpay-refund-modal-backdrop' ) : null;
	var messageEl    = document.getElementById( 'simpay-refund-message' );
	var fieldset     = document.getElementById( 'simpay-refund-fieldset' );
	var partialWrap  = document.getElementById( 'simpay-refund-partial-wrap' );
	var amountInput  = document.getElementById( 'simpay-refund-amount' );

	if ( ! modal || ! openBtn ) {
		return;
	}

	/**
	 * Opens the refund modal.
	 */
	function openModal() {
		// Flex, not block: the stylesheet centers the dialog with flexbox.
		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';

		// Reset state.
		hideMessage();
		fieldset.disabled = false;
		submitBtn.disabled = false;
		submitBtn.textContent = submitBtn.getAttribute( 'data-original-text' ) || submitBtn.textContent;

		var fullRadio = modal.querySelector( 'input[value="full"]' );
		if ( fullRadio ) {
			fullRadio.checked = true;
		}
		partialWrap.style.display = 'none';
		if ( amountInput ) {
			amountInput.value = '';
		}
	}

	/**
	 * Closes the refund modal.
	 */
	function closeModal() {
		modal.style.display = 'none';
		document.body.style.overflow = '';
	}

	/**
	 * Shows a message in the modal.
	 *
	 * @param {string} text    Message text.
	 * @param {string} type    Message type: 'success' or 'error'.
	 */
	function showMessage( text, type ) {
		messageEl.textContent = text;
		messageEl.className = 'simpay-refund-modal-message simpay-refund-modal-message--' + type;
		messageEl.style.display = 'block';
	}

	/**
	 * Hides the message element.
	 */
	function hideMessage() {
		messageEl.style.display = 'none';
		messageEl.textContent = '';
		messageEl.className = 'simpay-refund-modal-message';
	}

	// Store original button text.
	submitBtn.setAttribute( 'data-original-text', submitBtn.textContent );

	// Open modal.
	openBtn.addEventListener( 'click', openModal );

	// Close modal.
	closeBtn.addEventListener( 'click', closeModal );
	cancelBtn.addEventListener( 'click', closeModal );
	backdrop.addEventListener( 'click', closeModal );

	// Escape key closes modal.
	document.addEventListener( 'keydown', function( e ) {
		if ( 'Escape' === e.key && 'none' !== modal.style.display ) {
			closeModal();
		}
	} );

	// Full/partial radio toggle.
	var radios = modal.querySelectorAll( 'input[name="simpay_refund_type"]' );
	radios.forEach( function( radio ) {
		radio.addEventListener( 'change', function() {
			if ( 'partial' === this.value ) {
				partialWrap.style.display = 'block';
				if ( amountInput ) {
					amountInput.focus();
				}
			} else {
				partialWrap.style.display = 'none';
			}
		} );
	} );

	// Submit refund.
	submitBtn.addEventListener( 'click', function() {
		var refundType = modal.querySelector( 'input[name="simpay_refund_type"]:checked' );
		var amount;

		if ( ! refundType ) {
			return;
		}

		if ( 'full' === refundType.value ) {
			amount = config.maxRefundable;
		} else {
			amount = amountInput ? parseFloat( amountInput.value ) : 0;

			if ( ! amount || amount <= 0 ) {
				showMessage( config.i18n.invalidAmount, 'error' );
				return;
			}

			if ( amount > parseFloat( config.maxRefundable ) ) {
				showMessage( config.i18n.invalidAmount, 'error' );
				return;
			}
		}

		// Confirmation.
		if ( ! window.confirm( config.i18n.confirm ) ) {
			return;
		}

		// Disable form.
		hideMessage();
		fieldset.disabled = true;
		submitBtn.disabled = true;
		submitBtn.textContent = config.i18n.processing;

		// Build form data.
		var formData = new FormData();
		formData.append( 'action', 'simpay_process_refund' );
		formData.append( 'nonce', config.nonce );
		formData.append( 'transaction_id', config.transactionId );
		formData.append( 'amount', amount );

		fetch( config.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: formData,
		} )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( data ) {
				if ( data.success ) {
					showMessage( config.i18n.success, 'success' );

					// Reload page after a brief delay so user sees the message.
					setTimeout( function() {
						window.location.reload();
					}, 1500 );
				} else {
					var errorMsg = ( data.data && data.data.message )
						? data.data.message
						: config.i18n.error;

					showMessage( errorMsg, 'error' );
					fieldset.disabled = false;
					submitBtn.disabled = false;
					submitBtn.textContent = submitBtn.getAttribute( 'data-original-text' );
				}
			} )
			.catch( function() {
				showMessage( config.i18n.error, 'error' );
				fieldset.disabled = false;
				submitBtn.disabled = false;
				submitBtn.textContent = submitBtn.getAttribute( 'data-original-text' );
			} );
	} );
} )();

/**
 * Copy-to-clipboard buttons on the transaction detail page.
 *
 * Kept in its own IIFE so it runs even when the refund modal is absent.
 *
 * @since 4.17.4
 */
( function() {
	'use strict';

	var buttons = document.querySelectorAll( '.simpay-txn-copy' );

	if ( ! buttons.length ) {
		return;
	}

	function flagCopied( button ) {
		button.classList.add( 'simpay-txn-copy--copied' );
		setTimeout( function() {
			button.classList.remove( 'simpay-txn-copy--copied' );
		}, 1500 );
	}

	function copyText( button, text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			navigator.clipboard.writeText( text ).then(
				function() {
					flagCopied( button );
				},
				function() {}
			);
			return;
		}

		// Fallback for browsers without the async clipboard API.
		var textarea = document.createElement( 'textarea' );
		textarea.value = text;
		textarea.setAttribute( 'readonly', '' );
		textarea.style.position = 'absolute';
		textarea.style.left = '-9999px';
		document.body.appendChild( textarea );
		textarea.select();

		try {
			document.execCommand( 'copy' );
			flagCopied( button );
		} catch ( e ) {}

		document.body.removeChild( textarea );
	}

	for ( var i = 0; i < buttons.length; i++ ) {
		buttons[ i ].addEventListener( 'click', function() {
			var text = this.getAttribute( 'data-clipboard-text' ) || '';

			if ( text ) {
				copyText( this, text );
			}
		} );
	}
} )();
