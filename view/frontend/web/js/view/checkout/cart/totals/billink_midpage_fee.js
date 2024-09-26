define(
    [
        'ko',
        'Billink_Billink/js/view/checkout/summary/billink_midpage_fee'
    ],
    function (ko, Component) {
        'use strict';

        return Component.extend({
            isDisplayed: function () {
                return this.isFullMode();
            }
        });
    }
);
