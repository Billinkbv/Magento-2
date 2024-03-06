define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/model/customer',
    'mage/storage',
    'Magento_Checkout/js/model/full-screen-loader',
    'Billink_Billink/js/model/place-order'

], function ($, quote, urlBuilder, customer, storage, fullScreenLoader, placeOrderService) {
    'use strict';

    return function (paymentData,messageContainer) {
        let serviceUrl, payload, url, urlParams;
        payload = {
            cartId: quote.getQuoteId(),
            billingAddress: quote.billingAddress(),
            paymentMethod: paymentData
        };

        if (customer.isLoggedIn()) {
            url = '/billink/payment-data';
        } else {
            url = '/billink/guest-payment-data/:cartId';
            payload.email = quote.guestEmail;
        }
        urlParams = {
            cartId: quote.getQuoteId()
        };

        serviceUrl = urlBuilder.createUrl(url, urlParams);
        return placeOrderService(serviceUrl, payload, messageContainer);
    };
});
