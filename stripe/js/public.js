/**
 * Stripe Checkout public facing JavaScript
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

/* global sc_script */

(function ($) {
    'use strict';
    $(function() {

        // Including Parsley JS validation even though it might not be needed unless using add-ons.
        // TODO If there's a better way to include or exclude add it later.

        // Parsley JS prevents form submit by default. Stripe also suggests using a button click event
        // (not submit) to open the overlay in their custom implementation.
        // https://stripe.com/docs/checkout#integration-custom
        // So we need to explicitly call .validate() instead of auto-binding forms with data-parsley-form.
        // http://parsleyjs.org/doc/index.html#psly-usage-form

        var scFormList = $('.sc-checkout-form');

        scFormList.each(function() {
            var scForm = $(this);

            // Use Parsley's built-in validate event.
            // http://parsleyjs.org/doc/index.html#psly-events-overview
            scForm.parsley().subscribe('parsley:form:validate', function(formInstance) {

                if ( formInstance.isValid() ) {

                    // Get the "sc-id" ID of the current form as there may be multiple forms on the page.
                    var formId = scForm.data('sc-id') || '';

                    // Amount already preset in basic [stripe] shortcode (or default of 50).
                    var finalAmount = sc_script[formId].amount;

                    // If user-entered amount add-on active and found in form, use it's amount instead of preset/default.
                    var scUeaAmount = scForm.find('.sc-uea-custom-amount').val();

                    if ( scUeaAmount ) {

                        // Multiply amount to what Stripe needs unless zero-decimal currency used.
                        // Always round so there's no decimal. Stripe hates that.
                        if ( isZeroDecimalCurrency(sc_script[formId].currency) ) {
                            finalAmount = Math.round(scUeaAmount);
                        } else {
                            finalAmount = Math.round( parseFloat(scUeaAmount * 100) );
                        }
                    }

                    // Now pass to the Stripe Checkout handler.
                    // StripeCheckout object from Stripe's checkout.js.
                    // sc_script from localized script values from PHP.
                    // Reference https://stripe.com/docs/checkout#integration-custom for help.

                    var handler = StripeCheckout.configure({
                        key: sc_script[formId].key,
                        image: ( sc_script[formId].image != -1 ? sc_script[formId].image : '' ),
                        token: function(token, args) {

                            // At this point the Stripe checkout overlay is validated and submitted.

                            // Set the values on our hidden elements to pass via POST when submitting the form for payment.
                            scForm.find('.sc_stripeToken').val( token.id );
                            scForm.find('.sc_stripeEmail').val( token.email );
                            scForm.find('.sc_amount').val( finalAmount );

                            // Add shipping fields values if the shipping information is filled
                            if( ! $.isEmptyObject( args ) ) {
                                scForm.find('.sc-shipping-name').val(args.shipping_name);
                                scForm.find('.sc-shipping-country').val(args.shipping_address_country);
                                scForm.find('.sc-shipping-zip').val(args.shipping_address_zip);
                                scForm.find('.sc-shipping-state').val(args.shipping_address_state);
                                scForm.find('.sc-shipping-address').val(args.shipping_address_line1);
                                scForm.find('.sc-shipping-city').val(args.shipping_address_city);
                            }

                            //Unbind original form submit trigger before calling again to "reset" it and submit normally.
                            scForm.unbind('submit');
                            scForm.submit();

                            //Disable original payment button and change text for UI feedback while POST-ing to Stripe.
                            scForm.find('.sc_checkout')
                                .prop('disabled', true)
                                .find('span')
                                    .text('Please wait...');
                        }
                    });

                    handler.open({
                        name: ( sc_script[formId].name != -1 ? sc_script[formId].name : '' ),
                        description: ( sc_script[formId].description != -1 ? sc_script[formId].description : '' ),
                        amount: finalAmount,
                        currency: ( sc_script[formId].currency != -1 ? sc_script[formId].currency : 'USD' ),
                        panelLabel: ( sc_script[formId].panelLabel != -1 ? sc_script[formId].panelLabel : 'Pay {{amount}}' ),
                        billingAddress: ( sc_script[formId].billingAddress == 'true' || sc_script[formId].billingAddress == 1 ? true : false ),
                        shippingAddress: ( sc_script[formId].shippingAddress == 'true' || sc_script[formId].shippingAddress == 1 ? true : false ),
                        allowRememberMe: ( sc_script[formId].allowRememberMe == 1 || sc_script[formId].allowRememberMe == 'true' ?  true : false ),
                        email: ( sc_script[formId].email != -1 ?  sc_script[formId].email : '' )
                    });

                    // Let Stripe checkout overlay do the original form submit after it's ready.
                    formInstance.submitEvent.preventDefault();
                }
            });
        });

        // Zero-decimal currency check.
        // Just like sc_is_zero_decimal_currency() in PHP.
        // References sc_script['zero_decimal_currencies'] localized JS value.
        function isZeroDecimalCurrency(currency) {
            return ( $.inArray(currency.toUpperCase(), sc_script['zero_decimal_currencies']) > 1 );
        }
    });

}(jQuery));
