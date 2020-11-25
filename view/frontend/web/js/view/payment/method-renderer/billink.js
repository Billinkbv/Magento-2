define(
    [
        'underscore',
        'ko',
        'jquery',
        'mage/storage',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/get-totals',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/model/full-screen-loader',
        'Billink_Billink/js/view/checkout/billink_fee',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/totals',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/view/billing-address',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'mage/validation',
        'mage/translate'
    ],
    function (_, ko, $, storage, Component, quote, getTotalsAction, urlBuilder, fullScreenLoader, billinkFee, customer, totals, customerData, billingAddress, selectPaymentMethodAction, checkoutData) {
        'use strict';

        var cacheKey = 'billink_customer';

        var getData = function() {
            return customerData.get(cacheKey)();
        };

        var saveData = function (data) {
            customerData.set(cacheKey, data);
        };

        var resetData = function() {
            var data = {
                'selectedCustomerType': null,
            };
            saveData(data);
        };

        if ($.isEmptyObject(getData())) {
            resetData();
        }

        var billinkBillingStreetName = '';
        var billinkShippingStreetName = '';
        try {
            billinkBillingStreetName = quote.billingAddress().street[0] !== undefined ? quote.billingAddress().street[0] : '';
            billinkShippingStreetName = quote.shippingAddress().street[0] !== undefined ? quote.shippingAddress().street[0] : '';
        } catch (e) {
            // In case street or one of the fields isn't properly set just continue with the default empty values
        }

        // const regex = /\d+?\S+$/g;
        // const regexNumber = /[0-9]+/g;
        // const regexExt = /[a-zA-Z]+/g;

        // var houseNumberWithExtension = "";
        // var housenumber = "";
        // var extension = "";

        // houseNumberWithExtension = regex.exec(billinkBillingStreetName);
        // housenumber = regexNumber.exec(houseNumberWithExtension);
        // extension = regexExt.exec(houseNumberWithExtension);

        // if(extension == "null"){
        //     extension = "";
        // }

        // billinkBillingStreetName = billinkBillingStreetName.replace(houseNumberWithExtension,'');

        return Component.extend({
            lastDetectedMethod: null,
            additionalData: ko.observable({}),
            customerTypes: ko.observable([]),
            isAddressSameAsShipping: billingAddress().isAddressSameAsShipping,

            inputFields: {
                billink_chamber_of_commerce: ko.observable(''),
                billink_street: ko.observable(billinkBillingStreetName),
                billink_house_number: ko.observable(''),
                billink_house_extension: ko.observable(''),
                billink_customer_birthdate: ko.observable(''),
                billink_customer_sex: ko.observable(''),
                billink_delivery_address_street: ko.observable(billinkShippingStreetName),
                billink_delivery_address_housenumber: ko.observable(''),
                billink_delivery_address_housenumber_extension: ko.observable('')
            },

            dbSelectedCustomerType: window.checkoutConfig.quoteData.selected_workflow,
            dbSelectedPaymentMethod: window.checkoutConfig.quoteData.payment_method,
            isFeeActive: window.checkoutConfig.payment.billink.feeActive,

            defaults: {
                template: 'Billink_Billink/payment/form'
            },

            getCode: function () {
                return 'billink';
            },

            getLogo: function() {
                return window.checkoutConfig.payment.billink.logo;
            },

            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': this.additionalData()
                };
            },

            selectPaymentMethod: function () {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                this.updateCustomerTypeSelect();

                return true;
            },

            initialize: function() {
                this._super();

                this.updateCustomerTypeSelect();
            },

            updateCustomerTypeSelect: function () {
                if (!this.disablePaymentMethods()) {
                    this.selectedCustomerType(this.dbSelectedCustomerType);
                }
            },

            placeOrder: function() {
                if (!this.validate()) {
                    return;
                }

                var additionalData = Object.assign(this.additionalData(), {
                    'billink_street': this.inputFields.billink_street(),
                    'billink_house_number': this.inputFields.billink_house_number(),
                    'billink_house_extension': this.inputFields.billink_house_extension(),
                    'billink_validate_order': true
                });
                if (this.isSelectedType('B')) {
                    additionalData = Object.assign(additionalData, {
                        'billink_chamber_of_commerce': this.inputFields.billink_chamber_of_commerce()
                    });
                }
                if (this.isSelectedType('P')) {
                    additionalData = Object.assign(additionalData, {
                        'billink_customer_birthdate': this.inputFields.billink_customer_birthdate(),
                        'billink_customer_sex': this.inputFields.billink_customer_sex()
                    });
                }
                if (!this.isAddressSameAsShipping()) {
                    additionalData = Object.assign(additionalData, {
                        'billink_delivery_address_street': this.inputFields.billink_delivery_address_street(),
                        'billink_delivery_address_housenumber': this.inputFields.billink_delivery_address_housenumber(),
                        'billink_delivery_address_housenumber_extension': this.inputFields.billink_delivery_address_housenumber_extension()
                    });
                }

                this.additionalData(additionalData);
                this._super();
            },

            refreshMethod: function (data) {
                var serviceUrl,
                    paymentData = data ? data : quote.paymentMethod();

                delete(paymentData['title']);

                fullScreenLoader.startLoader();

                if (customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/selected-payment-method', {});
                } else {
                    serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/selected-payment-method', {
                        cartId: quote.getQuoteId()
                    });
                }

                var payload = {
                    cartId: quote.getQuoteId(),
                    method: paymentData
                };

                return storage.post(
                    serviceUrl,
                    JSON.stringify(payload),
                    false
                ).done(function () {
                    billinkFee.canShow(quote.paymentMethod().method == 'billink');
                    getTotalsAction([]);
                    fullScreenLoader.stopLoader();
                });
            },

            isSelectedType: function (type) {
                return this.selectedCustomerType() === type;
            },

            initDatetime: function (elements) {
                $(elements).datepicker({
                    changeMonth: true,
                    changeYear: true,
                    yearRange: "-100:+00",
                    dateFormat: "dd-mm-yy"
                });
            },

            showWorkflowOption: function (workflowType) {
                var workflowTotal = window.checkoutConfig.payment.billink.workflow['workflow_' + workflowType].max_amount;
                var total = totals.getSegment('grand_total').value;

                return total <= workflowTotal;
            },

            disablePaymentMethods: function () {
                var self = this;
                var result = true;

                this.customerTypes().forEach(function (workflow) {
                    if(self.showWorkflowOption(workflow.value)) {
                        result = false;
                    }
                });

                return result;
            },

            isAlternateDeliveryAddressAllowed: function() {
                return window.checkoutConfig.payment.billink.isAlternateDeliveryAddressAllowed;
            },

            isChecked: ko.computed(function () {
                if (quote.paymentMethod()){
                    return quote.paymentMethod().method;
                }

                var quotePayment = window.checkoutConfig.quoteData.payment_method;

                return quotePayment ? quotePayment : null;
            }),

            selectedCustomerType: ko.observable(''),

            changePaymentMethod: function () {
                var paymentMethod = quote.paymentMethod().method;

                if (this.isFeeActive && paymentMethod != this.lastDetectedMethod) {
                    if (paymentMethod == 'billink' || this.lastDetectedMethod == 'billink' || (totals.getSegment('billink_fee') && this.lastDetectedMethod === null)) {
                        this.refreshMethod();
                    }
                    this.lastDetectedMethod = paymentMethod;
                }
            },

            selectWorkflowType: function (type) {
                if (!type) {
                    return;
                }

                var obj = getData();
                var quotePaymentMethod = quote.paymentMethod() ? quote.paymentMethod().method : this.dbSelectedPaymentMethod;

                obj.selectedCustomerType = type;
                this.additionalData({billink_customer_type: type});
                saveData(obj);

                if (this.isFeeActive && quotePaymentMethod && quotePaymentMethod == 'billink') {
                    this.lastDetectedMethod = null;
                    quote.paymentMethod(this.getData());
                }
            },

            initObservable: function () {
                this._super();

                quote.paymentMethod.subscribe(this.changePaymentMethod.bind(this));
                this.selectedCustomerType.subscribe(this.selectWorkflowType.bind(this));
                this.customerTypes(this.getWorkflowTypes());

                return this;
            },

            getWorkflowTypes: function () {
                var customerTypes = [];
                var workflow = window.checkoutConfig.payment.billink.workflow;
                var workflowPrefix = window.checkoutConfig.payment.billink.workflowTypePrefix;
                Object.keys(workflow).forEach(function(key) {
                    var obj = _.clone(workflow[key]);
                    obj.value = key.substr(workflowPrefix.length);

                    customerTypes.push(obj);
                });

                return customerTypes;

            },

            validate: function() {
                var $form = $('#form-billink');

                return $form.validation() && $form.validation('isValid');
            }
        });
    }
);