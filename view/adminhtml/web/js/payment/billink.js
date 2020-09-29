/*browser:true*/
/*global define*/
define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'mage/translate'
], function ($, ko, _, Component) {
    'use strict';

    return Component.extend({
        customerTypes: ko.observable(),
        selectedCustomerType: ko.observable('P'),
        additionalData: ko.observable({}),
        grandTotal: ko.observable(),

        defaults: {
            workflow: '',
            workflowPrefix: ''
        },

        inputFields: {
            billink_chamber_of_commerce: ko.observable(''),
            billink_street: ko.observable(''),
            billink_house_number: ko.observable(''),
            billink_house_extension: ko.observable(''),
            billink_customer_birthdate: ko.observable(''),
            billink_customer_sex: ko.observable(''),
            billink_delivery_address_street: ko.observable(''),
            billink_delivery_address_housenumber: ko.observable(''),
            billink_delivery_address_housenumber_extension: ko.observable('')
        },

        getCode: function() {
            return 'billink';
        },

        initialize: function(data) {
            this._super();
            $('#form-billink input[type="text"]').each(function(index, elem){
                elem.disabled = false;
            });
            this.grandTotal(data.grandTotal);
            this.placeOrder();


            console.log(window.order);
            console.log(window.order.loadArea(false, false, {test:'test'}));
        },

        initObservable: function () {
            this._super();

            this.selectedCustomerType.subscribe(this.selectWorkflowType.bind(this));
            this.customerTypes(this.getWorkflowTypes());

            return this;
        },

        selectWorkflowType: function (type) {
            if (!type) {
                return;
            }

            this.additionalData({billink_customer_type: type});
        },


        getWorkflowTypes: function () {
            var customerTypes = [];
            var workflow = this.workflow;
            var workflowPrefix =this.workflowPrefix;
            Object.keys(workflow).forEach(function(key) {
                var obj = _.clone(workflow[key]);
                obj.value = key.substr(workflowPrefix.length);

                customerTypes.push(obj);
            });

            return customerTypes;

        },

        isSelectedType: function (type) {
            return this.selectedCustomerType() === type;
        },

        initDatetime: function (elements) {
            $(elements).datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+00",
                dateFormat: "yy-mm-dd"
            });
        },

        placeOrder: function() {
            var self = this;

            if (!this.validate()) {
                return;
            }

            this.inputFields.billink_street.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_street': self.inputFields.billink_street()
                }));
            });

            this.inputFields.billink_house_number.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_house_number': self.inputFields.billink_house_number()
                }));
            });

            this.inputFields.billink_house_extension.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_house_extension': self.inputFields.billink_house_extension()
                }));
            });

            this.additionalData(Object.assign(self.additionalData(), {
                'billink_validate_order': true
            }));


            if (this.isSelectedType('B')) {
                this.inputFields.billink_chamber_of_commerce.subscribe(function () {
                    self.additionalData(Object.assign(self.additionalData(), {
                        'billink_chamber_of_commerce': self.inputFields.billink_chamber_of_commerce()
                    }));
                });
            }
            if (this.isSelectedType('P')) {
                this.inputFields.billink_customer_birthdate.subscribe(function () {
                    self.additionalData(Object.assign(self.additionalData(), {
                        'billink_customer_birthdate': self.inputFields.billink_customer_birthdate()
                    }));
                });
                this.inputFields.billink_customer_sex.subscribe(function () {
                    self.additionalData(Object.assign(self.additionalData(), {
                        'billink_customer_sex': self.inputFields.billink_customer_sex()
                    }));
                });
            }

            this.inputFields.billink_delivery_address_street.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_delivery_address_street': self.inputFields.billink_delivery_address_street()
                }));
            });
            this.inputFields.billink_delivery_address_housenumber.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_delivery_address_housenumber': self.inputFields.billink_delivery_address_housenumber()
                }));
            });
            this.inputFields.billink_delivery_address_housenumber_extension.subscribe(function () {
                self.additionalData(Object.assign(self.additionalData(), {
                    'billink_delivery_address_housenumber_extension': self.inputFields.billink_delivery_address_housenumber_extension()
                }));
            });

            this._super();
        },

        validate: function() {
            var $form = $('#form-billink > form');

            return $form.validation() && $form.validation('isValid');
        }
    });
});
