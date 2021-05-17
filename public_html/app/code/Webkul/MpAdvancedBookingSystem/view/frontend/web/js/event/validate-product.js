define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'Webkul_MpAdvancedBookingSystem/js/event/catalog-add-to-cart'
], function ($) {
    'use strict';

    $.widget('mage.productValidate', {
        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested'
        },

        /**
         * Uses Magento's validation widget for the form object.
         * @private
         */
        _create: function () {
            var bindSubmit = this.options.bindSubmit;

            this.element.validation({
                radioCheckboxClosest: this.options.radioCheckboxClosest,

                /**
                 * Uses catalogAddToCart widget as submit handler.
                 * @param {Object} form
                 * @returns {Boolean}
                 */
                submitHandler: function (form) {
                    var jqForm = $(form).catalogAddToCart({
                        bindSubmit: bindSubmit
                    });

                    jqForm.catalogAddToCart('submitForm', jqForm);

                    return false;
                }
            });
        }
    });

    return $.mage.productValidate;
});