/**
 * Since we dynamically load our blocks, wordpress.org cannot pick them up properly.
 * This file solely exists to let WordPress know what blocks we are currently using.
 *
 * @since {next}
 */

/* eslint-disable no-undef */

registerBlockType('simpay/payment-form', {
	title : 'WP Simple Pay - Payment Form'
})
registerBlockType('simpay/manage-subscriptions-block', {
	title : 'WP Simple Pay - Manage Subscriptions'
})