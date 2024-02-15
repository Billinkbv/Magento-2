/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'billink_midpage',
                component: 'Billink_Billink/js/view/payment/method-renderer/billink-midpage'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
