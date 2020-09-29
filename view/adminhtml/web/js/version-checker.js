/*browser:true*/
/*global define*/
define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate'
], function ($, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            versionText: ko.observable(''),
            loading: ko.observable(true),
            isError: ko.observable(false)
        },

        initialize: function () {
            this._super();

            this.check();
        },

        check: function () {
            var me = this;

            $.ajax({
                url: this.checkUrl,
                data: {form_key: window.FORM_KEY},
                type: 'POST',
                success: function(response) {
                    me.loading(false);

                    if (response.error) {
                        me.isError(true);
                        me.versionText($.mage.__('There was an error from server. Please refresh the page.'));
                        return;
                    }

                    if (!response.isUpToDate) {
                        me.isError(true);
                        me.versionText($.mage.__('Version %1 is available!').replace('%1', response.version));
                        return;
                    }

                    me.isError(false);
                    me.versionText(response.version);
                }
            });
        }
    });
});