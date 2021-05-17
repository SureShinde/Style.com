define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function ($, $t) {
    'use strict';

    $.widget(
        'mage.catalogAddToCart',
        {
            options: {
                processStart: null,
                processStop: null,
                bindSubmit: true,
                minicartSelector: '[data-block="minicart"]',
                messagesSelector: '[data-placeholder="messages"]',
                productStatusSelector: '.stock.available',
                addToCartButtonSelector: '.action.tocart',
                addToCartButtonDisabledClass: 'disabled',
                addToCartButtonTextWhileAdding: '',
                addToCartButtonTextAdded: '',
                addToCartButtonTextDefault: '',
                product_type: "booking"
            },

            /**
             * @inheritdoc
             */
            _create: function () {
                if (this.options.bindSubmit) {
                    this._bindSubmit();
                }
            },

            /**
             * @private
             */
            _bindSubmit: function () {
                var self = this;

                this.element.on(
                    'submit',
                    function (e) {
                        e.preventDefault();
                        self.submitForm($(this));
                    }
                );
            },

            /**
             * @return {Boolean}
             */
            isLoaderEnabled: function () {
                return this.options.processStart && this.options.processStop;
            },

            /**
             * Handler for the form 'submit' event
             *
             * @param {Object} form
             */
            submitForm: function (form) {
                var addToCartButton, self = this;
                var flag = 1;
                if (self.options.product_type!=="rentalbooking" && self.options.product_type!=="tablebooking") {
                    form.find('.product-custom-option').each(
                        function (params) {
                            var thisObj = $(this);
                            if (thisObj.val() && flag) {
                                var qty = thisObj.val();
                                var optionId = $(this).attr('data-option-id');
                                var optionValId = $(this).attr('data-option-val-id');
                                var dataForm = form.serializeArray();
                                if (self.options.product_type=="hotelbooking") {
                                    var hotelQty = self.calculateQty();
                                    dataForm.push({name: 'hotel_qty', value: hotelQty});
                                } else {
                                    dataForm.push({name: 'qty', value: qty});
                                }
                                $.each(
                                    dataForm,
                                    function (i, field) {
                                        if (field.name.indexOf("booking_options[") >= 0) {
                                            if (field.name != 'booking_options['+optionValId+'][qty]') {
                                                dataForm[i] = '{}';
                                            }
                                        }
                                        if (field.name == 'options['+optionId+'][]' && field.value != optionValId) {
                                            dataForm[i] = '{}';
                                        }
                                    }
                                );
                                var newSubmittedFormData = $.param(dataForm);

                                if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                                    self.element.off('submit');
                                    // disable 'Add to Cart' button
                                    addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                                    addToCartButton.prop('disabled', true);
                                    addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                                    form.submit();
                                } else {
                                    self.ajaxSubmit(form, newSubmittedFormData);
                                }
                                flag = 0;
                            }
                        }
                    );
                    if (flag) {
                        if (form.has('input[type="file"]').length && form.find('input[type="file"]').val() !== '') {
                            self.element.off('submit');
                            // disable 'Add to Cart' button
                            addToCartButton = $(form).find(this.options.addToCartButtonSelector);
                            addToCartButton.prop('disabled', true);
                            addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                            form.submit();
                        } else {
                            self.ajaxSubmit(form);
                        }
                    }
                } else {
                    self.ajaxSubmit(form);
                }
            },

            /**
             * @param {String} form
             */
            ajaxSubmit: function (form, newSubmittedFormData='') {
                var self = this;

                $(self.options.minicartSelector).trigger('contentLoading');
                self.disableAddToCartButton(form);
                if (!newSubmittedFormData) {
                    newSubmittedFormData = form.serialize();
                }
                $.ajax(
                    {
                        url: form.attr('action'),
                        data: newSubmittedFormData,
                        type: 'post',
                        dataType: 'json',

                        /**
                         * @inheritdoc
                         */
                        beforeSend: function () {
                            if (self.isLoaderEnabled()) {
                                $('body').trigger(self.options.processStart);
                            }
                        },

                        /**
                         * @inheritdoc
                         */
                        success: function (res) {
                            var eventData, parameters;

                            $(document).trigger('ajax:addToCart', form.data().productSku);

                            if (self.isLoaderEnabled()) {
                                $('body').trigger(self.options.processStop);
                            }

                            if (res.backUrl) {
                                eventData = {
                                    'form': form,
                                    'redirectParameters': []
                                };
                                // trigger global event, so other modules will be able add parameters to redirect url
                                $('body').trigger('catalogCategoryAddToCartRedirect', eventData);

                                if (eventData.redirectParameters.length > 0) {
                                    parameters = res.backUrl.split('#');
                                    parameters.push(eventData.redirectParameters.join('&'));
                                    res.backUrl = parameters.join('#');
                                }
                                window.location = res.backUrl;

                                return;
                            }

                            if (res.messages) {
                                $(self.options.messagesSelector).html(res.messages);
                            }

                            if (res.minicart) {
                                $(self.options.minicartSelector).replaceWith(res.minicart);
                                $(self.options.minicartSelector).trigger('contentUpdated');
                            }

                            if (res.product && res.product.statusText) {
                                $(self.options.productStatusSelector)
                                .removeClass('available')
                                .addClass('unavailable')
                                .find('span')
                                .html(res.product.statusText);
                            }
                            self.enableAddToCartButton(form);
                        }
                    }
                );
            },

            /**
             * @param {String} form
             */
            disableAddToCartButton: function (form) {
                var addToCartButtonTextWhileAdding = this.options.addToCartButtonTextWhileAdding || $t('Adding...'),
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                addToCartButton.addClass(this.options.addToCartButtonDisabledClass);
                addToCartButton.find('span').text(addToCartButtonTextWhileAdding);
                addToCartButton.attr('title', addToCartButtonTextWhileAdding);
            },

            /**
             * @param {String} form
             */
            enableAddToCartButton: function (form) {
                var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added'),
                self = this,
                addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                addToCartButton.find('span').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);

                setTimeout(
                    function () {
                        if (self.options.product_type == "booking") {
                            var addToCartButtonTextDefault = $t('Book Now');
                        } else if (self.options.product_type == "rentalbooking") {
                            var addToCartButtonTextDefault = $t('Rent Now');
                        } else if (self.options.product_type == "hotelbooking") {
                            var addToCartButtonTextDefault = $t('Book Now');
                        } else {
                            var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');
                        }

                        addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                        addToCartButton.find('span').text(addToCartButtonTextDefault);
                        addToCartButton.attr('title', addToCartButtonTextDefault);
                    },
                    1000
                );
            },

            calculateQty: function () {
                var qty = 0;
                var bookingFrom = Date.parse($("input#wk-bk-select-date-from").val());
                var bookingTo = Date.parse($("input#wk-bk-select-date-to").val());

                var noOfDays = Math.round((bookingTo-bookingFrom)/(1000*60*60*24));
                qty = qty + noOfDays;

                return qty;
            }
        }
    );

    return $.mage.catalogAddToCart;
});
