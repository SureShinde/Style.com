define([
    'jquery',
    'mage/mage',
    'Magento_Catalog/product/view/validation',
    'Webkul_MpAdvancedBookingSystem/js/catalog-add-to-cart'
], function ($) {
    'use strict';

    $.widget('mage.productValidate', {
        options: {
            bindSubmit: false,
            radioCheckboxClosest: '.nested',
            product_type: 'booking'
        },

        /**
         * Uses Magento's validation widget for the form object.
         *
         * @private
         */
        _create: function () {
            var bindSubmit = this.options.bindSubmit;
            var product_type = this.options.product_type;

            this.element.validation(
                {
                    radioCheckboxClosest: this.options.radioCheckboxClosest,

                    /**
                     * Uses catalogAddToCart widget as submit handler.
                     *
                     * @param   {Object} form
                     * @returns {Boolean}
                     */
                    submitHandler: function (form) {
                        var jqForm = $(form).catalogAddToCart(
                            {
                                bindSubmit: bindSubmit,
                                product_type: product_type
                            }
                        );

                        jqForm.catalogAddToCart('submitForm', jqForm);

                        return false;
                    }
                }
            );
        }
    });

    return $.mage.productValidate;
});