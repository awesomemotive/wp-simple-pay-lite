/**
 * Stripe Checkout public facing JavaScript
 *
 * @package SC
 * @author  Phil Derksen <pderksen@gmail.com>, Nick Young <mycorpweb@gmail.com>
 */

(function ($) {
    'use strict';
    $(function() {

        // Including Parsley JS validation even though it might not be needed unless using add-ons.
        // If there's a better way figure it out later.

        $('.sc-checkout-form').each(function() {
            var scForm = $(this);

            // Unbind original submit handler since we're never going to post the form.
            scForm.submit(function(event) {
                event.preventDefault();
            });

            // Now use Parsley's built-in validate event.
            scForm.parsley().subscribe('parsley:form:validate', function(formInstance) {

                if ( formInstance.isValid() ) {

                    // Get the "sc-id" ID of the current form as there may be multiple forms on the page.
                    var formId = scForm.data('sc-id') || '';

                    //TODO
                    var finalAmount = sc_script[formId].amount;

                    console.log(sc_script[formId]);
                    console.log(finalAmount);

                    console.log(scForm.find('.sc-uea-custom-amount'));

                    //TODO If user-entered amount add-on and field found, use it instead of pre-set amount.
                    if ( scForm.find('.sc-uea-custom-amount').length ) {

                        //TODO Currency convert?

                        //finalAmount = scForm.find('.sc-uea-custom-amount');
                        finalAmount = parseFloat( scForm.find('.sc-uea-custom-amount').val() * 100 );
                    }

                    console.log(finalAmount);

                    // Sanitize amount, then pass to the Stripe Checkout handler.
                    // StripeCheckout object from Stripe's checkout.js.
                    // sc_script from localized script values from PHP.
                    // Reference https://stripe.com/docs/checkout#integration-custom for help.

                    var handler = StripeCheckout.configure({
                        key: sc_script[formId].key,
                        image: ( sc_script[formId].image != -1 ? sc_script[formId].image : '' ),
                        token: function(token, args) {

                            // Set the values on our hidden elements to pass when submitting the form for payment
                            scForm.find('.sc_stripeToken').val( token.id );

                            //TODO Amount and email getting set twice?
                            //TODO Test pre-filled email.
                            //TODO scForm.find('.sc_amount').val( finalAmount );
                            //scForm.find('.sc_stripeEmail').val( token.email );

                            // Add shipping fields values if the shipping information is filled
                            if( ! $.isEmptyObject( args ) ) {
                                scForm.find('.sc-shipping-name').val(args.shipping_name);
                                scForm.find('.sc-shipping-country').val(args.shipping_address_country);
                                scForm.find('.sc-shipping-zip').val(args.shipping_address_zip);
                                scForm.find('.sc-shipping-state').val(args.shipping_address_state);
                                scForm.find('.sc-shipping-address').val(args.shipping_address_line1);
                                scForm.find('.sc-shipping-city').val(args.shipping_address_city);
                            }
                        }
                    });

                    handler.open({
                        name: ( sc_script[formId].name != -1 ? sc_script[formId].name : '' ),
                        description: ( sc_script[formId].description != -1 ? sc_script[formId].description : '' ),
                        //TODO amount: sc_script[formId].amount,
                        amount: finalAmount,
                        currency: ( sc_script[formId].currency != -1 ? sc_script[formId].currency : 'USD' ),
                        panelLabel: ( sc_script[formId].panelLabel != -1 ? sc_script[formId].panelLabel : 'Pay {{amount}}' ),
                        billingAddress: ( sc_script[formId].billingAddress == 'true' || sc_script[formId].billingAddress == 1 ? true : false ),
                        shippingAddress: ( sc_script[formId].shippingAddress == 'true' || sc_script[formId].shippingAddress == 1 ? true : false ),
                        allowRememberMe: ( sc_script[formId].allowRememberMe == 1 || sc_script[formId].allowRememberMe == 'true' ?  true : false ),
                        email: ( sc_script[formId].email != -1 ?  sc_script[formId].email : '' )
                    });
                }
            });
        });
    });
}(jQuery));
