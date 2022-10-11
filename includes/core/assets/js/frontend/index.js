/* global jQuery, _, Stripe */

/**
 * Internal dependencies.
 */
import { default as hooks, doAction } from '@wpsimplepay/hooks';
import * as paymentForms from '@wpsimplepay/payment-forms';
import { default as legacyHelpers } from './utils/legacy.js';
import './payment-forms';

/**
 * WP Simple Pay API.
 *
 * @todo Create automatically with Webpack.
 */
window.wpsp = {
	hooks,
	paymentForms,
	initPaymentForm,
};

/**
 * Legacy API.
 */
window.simpayApp = {
	formCount: 0,
	spFormElList: {},
	spFormData: {},
	spFormElems: {},
	...legacyHelpers,
};

/**
 * Initializes Payment Forms on the current page.
 */
function initPaymentForms() {
	const $paymentForms = jQuery( document.body ).find(
		'.simpay-checkout-form:not(.simpay-update-payment-method)'
	);

	window.simpayApp.spFormElList = $paymentForms;

	$paymentForms.each( function () {
		window.simpayApp.formCount++;
		initPaymentForm( jQuery( this ) );
	} );
}

/**
 * Initializes a Payment Form given a jQuery object.
 *
 * @param {jQuery} $paymentForm jQuery Payment Form object.
 * @param {Object|boolean} __unstableFormVars Form data. Will be pulled from page source if not set.
 */
function initPaymentForm( $paymentForm, __unstableFormVars = false ) {
	// Add the form instance count to the wrapper element to allow selectors
	// such as #payment-form-123[data-simpay-form-instance="2"].
	//
	// This ensures we can query for a specific element if a form appears more
	// than once on a page. We cannot always access via spFormElem/$paymentForm
	// due to Overlay forms being moved around the DOM.
	//
	// @link https://github.com/wpsimplepay/wp-simple-pay-pro/pull/766
	const { formCount } = window.simpayApp;
	$paymentForm.attr( 'data-simpay-form-instance', formCount );

	// Retrieve localized form data.
	let paymentFormData, formId;

	if ( false === __unstableFormVars ) {
		const forms = window.simplePayForms;

		formId = $paymentForm.data( 'simpay-form-id' );
		paymentFormData = forms[ formId ];
	} else {
		paymentFormData = __unstableFormVars;
		formId = __unstableFormVars.id;
	}

	const {
		type: formType,
		form: { prices, livemode, config = {} },
	} = paymentFormData;

	const {
		taxRates = [],
		paymentMethods = [],
		taxStatus = 'fixed-global',
	} = config;

	// Merge localized form data in to a semi-simplified object.
	// Maintained for backwards compatibility.
	const formData = {
		formId,
		formInstance: formCount,
		quantity: 1,
		isValid: true,
		stripeParams: {
			...paymentFormData.stripe.strings,
			...paymentFormData.stripe.bools,
		},
		prices,
		...paymentFormData.form.bools,
		...paymentFormData.form.integers,
		...paymentFormData.form.i18n,
		...paymentFormData.form.strings,
		...paymentFormData.form.config,
	};

	// Attach legacy form data to the Payment Form jQuery object so
	// it is available for backwards compatibility.
	$paymentForm.__unstableLegacyFormData = formData;

	// Bind the Payment Form's type settings.
	const formTypeSettings = paymentForms.getPaymentFormType( formType );

	_.each( formTypeSettings, ( setting, settingKey ) => {
		// Bind the instance of the PaymentForm to the first argument of setting callbacks.
		// @link https://underscorejs.org/#bind
		$paymentForm[ settingKey ] = _.isFunction( setting )
			? _.bind( setting, $paymentForm, $paymentForm )
			: setting;
	} );

	// Attach the ID.
	$paymentForm.id = formId;

	// Attach form state information to the Payment Form jQuery object.
	$paymentForm.state = {
		isValid: true,
		customAmount: false,
		coupon: false,
		// Ensure a price is always available.
		price: _.find( prices, ( { default: isDefault } ) => {
			return true === isDefault;
		} ),
		paymentMethod: _.first( paymentMethods ),
		taxRates,
		taxStatus,
		paymentMethods,
		livemode,
		displayType: formData.formDisplayType,
	};

	// Attach a form state setter to the Payment Form jQuery object.
	$paymentForm.setState = function ( updatedState ) {
		$paymentForm.state = {
			...$paymentForm.state,
			...updatedState,
		};
	};

	// Attach a helper to get the form data/state.
	$paymentForm.getFormData = function () {
		const _formData = {
			...$paymentForm.__unstableLegacyFormData,
			...$paymentForm.state,
		};

		// Remove additional data that is not needed and may trigger WAF rules.
		const {
			order: _o,
			customer: _cus,
			paymentMethods: _pms,
			...cleanFormData // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Operators/Destructuring_assignment#rest_property
		} = _formData;

		return JSON.stringify( cleanFormData );
	};

	// Attach a Stripe instance to the Payment Form jQuery object.
	const {
		key: publishableKey,
		stripe_api_version: apiVersion,
		elementsLocale,
	} = paymentFormData.stripe.strings;

	$paymentForm.stripeInstance = Stripe( publishableKey, {
		apiVersion,
		locale: elementsLocale || 'auto',
	} );

	/**
	 * Allows further setup of a Payment Form.
	 *
	 * @since 4.2.0
	 *
	 * @param {PaymentForm} $paymentForm
	 */
	doAction( 'simpaySetupPaymentForm', $paymentForm );

	// Backwards compatibility.
	window.simpayApp.spFormData[ formId ] = formData;
	window.simpayApp.spFormElems[ formId ] = $paymentForm;

	jQuery( document.body )
		.trigger( 'simpayCoreFormVarsInitialized', [ $paymentForm, formData ] )
		.trigger( 'simpayBindCoreFormEventsAndTriggers', [
			$paymentForm,
			formData,
		] )
		.trigger( 'simpaySetupCoreForm', [ $paymentForm ] );
}

jQuery( () => initPaymentForms() );
