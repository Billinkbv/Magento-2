define(
    [
        'Magento_Checkout/js/view/summary/abstract-total',
        'Magento_Checkout/js/model/quote',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/totals'
    ],
    function (Component, quote, priceUtils, totals) {
        "use strict";
        return Component.extend({
            defaults: {
                isFullTaxSummaryDisplayed: window.checkoutConfig.isFullTaxSummaryDisplayed || false,
                template: 'Billink_Billink/checkout/summary/billink_fee'
            },

            totals: quote.getTotals(),

            isTaxDisplayedInGrandTotal: window.checkoutConfig.includeTaxInGrandTotal || false,

            isDisplayed: function () {
                return this.isFullMode();
            },

            hasTotal: function () {
                if (this.totals()) {
                    return !!totals.getSegment('billink_midpage_fee');
                }

                return false;
            },

            getTitle: function() {
                return window.checkoutConfig.payment.billink_midpage.feeLabel;
            },

            getValue: function () {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('billink_midpage_fee').value;
                }
                return this.getFormattedPrice(price);
            },

            getBaseValue: function () {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('billink_midpage_fee').value;
                }
                return this.getFormattedPrice(price);
            },

            shouldDisplay: function () {
                var price = 0;
                if (this.hasTotal()) {
                    price = totals.getSegment('billink_midpage_fee').value;
                }

                return price;
            }
        });
    }
);
