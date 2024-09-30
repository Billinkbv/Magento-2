/* @api */

define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/step-navigator',
    'Billink_Billink/js/action/place-order',
    'Billink_Billink/js/action/redirect-on-success',
    'Magento_Customer/js/customer-data',
    'Billink_Billink/js/view/checkout/billink_midpage_fee'
], function (
    $,
    Component,
    additionalValidators,
    stepNavigator,
    placeOrderAction,
    redirectOnSuccessAction,
    customerData,
    billinkFee
) {
    'use strict';

    return Component.extend({

        isFeeActive: window.checkoutConfig.payment.billink_midpage.feeActive,
        defaults: {
            template: 'Billink_Billink/payment/midpage-form'
        },

        /**
         * Place order.
         */
        placeOrder: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }

            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function (data) {
                        customerData.invalidate(['cart']);
                        redirectOnSuccessAction.execute(data.redirect_url);
                    }
                );

                return true;
            }

            return false;
        },

        getLogo: function() {
            return window.checkoutConfig.payment.billink_midpage.logo;
        },

        /**
         * @return {*}
         */
        getPlaceOrderDeferredObject: function () {
            return $.when(
                placeOrderAction(this.getData(), this.messageContainer)
            );
        },

        /**
         * @return void
         */
        navigateToShippingStep: function () {
            stepNavigator.navigateTo('shipping');
        }
    });
});

