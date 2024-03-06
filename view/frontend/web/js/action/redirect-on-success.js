/**
 * @api
 */
define(
    [
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader'
    ],
    function (url, fullScreenLoader) {
        'use strict';

        return {
            /**
             * Provide redirect to page
             */
            execute: function (redirectUrl) {
                fullScreenLoader.startLoader();
                window.location.replace(redirectUrl);
            }
        };
    }
);
