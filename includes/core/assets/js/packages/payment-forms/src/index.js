/**
 * Payment Form.
 *
 * @typedef {Object} PaymentForm
 *
 * @property {(paymentForm: PaymentForm) => void} setup A callback for setting up the payment form type.
 * @property {(paymentForm: PaymentForm) => void} submit A callback for submitting the payment form type.
 * @property {(paymentForm: PaymentForm) => void} enable A callback for enabling the payment form type.
 * @property {(paymentForm: PaymentForm) => void} disable A callback for disabling the payment form type.
 * @property {(paymentForm: PaymentForm, error: Object|string) => void} error A callback for display payment form type errors.
 * @property {Stripe} stripeInstance stripe.js Stripe object.
 * @property {Object} state Payment Form state.
 * @property {(updatedState: Object) => Object} setState Payment form state setter.
 * @property {Object} __unstableLegacyFormData Legacy payment form data.
 */

/**
 * Payment Form type.
 *
 * @typedef {Object} PaymentFormType
 *
 * @property {(paymentForm: PaymentForm) => void} setup A callback for setting up the payment form type.
 * @property {(paymentForm: PaymentForm) => void} submit A callback for submitting the payment form type.
 * @property {(paymentForm: PaymentForm) => void} enable A callback for enabling the payment form type.
 * @property {(paymentForm: PaymentForm) => void} disable A callback for disabling the payment form type.
 * @property {(paymentForm: PaymentForm, error: Object|string) => void} error A callback for display payment form type errors.
 */

/**
 * Payment Method.
 *
 * @typedef {Object} PaymentMethod
 *
 * @property {(paymentForm: PaymentForm) => void} setup A callback for setting up the form.
 * @property {(paymentForm: PaymentForm) => void} submit A callback for submitting the form.
 */

export * from './api';
export * from './utils.js';
