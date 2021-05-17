define([
    'jquery',
    'underscore',
    'mage/template',
    'priceUtils',
    'priceBox',
    'jquery/ui'
], function ($, _, mageTemplate, utils) {
    'use strict';

    var globalOptions = {
        productId: null,
        priceHolderSelector: '.price-box', //data-role="priceBox"
        optionsSelector: '.product-custom-option',
        optionConfig: {},
        optionHandlers: {},
        controlContainer: 'dd',
        deafultFlag: 1
    };

    /**
     * Custom option preprocessor
     * @param  {jQuery} element
     * @param  {Object} optionsConfig - part of config
     * @return {Object}
     */
    function defaultGetOptionValue(element, optionsConfig)
    {
        var changes = {},
            optionValue = element.val(),
            optionId = utils.findOptionId(element[0]),
            optionName = element.prop('name'),
            optionType = element.prop('type'),
            optionConfig = optionsConfig[optionId],
            optionHash = optionName;

        switch (optionType) {
            case 'text':
            case 'textarea':
                changes[optionHash] = optionValue ? optionConfig.prices : {};
                break;

            case 'radio':
                if (element.is(':checked')) {
                    changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                }
                break;

            case 'select-one':
                changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                break;

            case 'select-multiple':
                _.each(optionConfig, function (row, optionValueCode) {
                    optionHash = optionName + '##' + optionValueCode;
                    changes[optionHash] = _.contains(optionValue, optionValueCode) ? row.prices : {};
                });
                break;

            case 'checkbox':
                optionHash = optionName + '##' + optionValue;
                changes[optionHash] = element.is(':checked') ? optionConfig[optionValue].prices : {};
                break;

            case 'file':
                // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                changes[optionHash] = optionValue || element.prop('disabled') ? optionConfig.prices : {};
                break;
        }

        return changes;
    }

    $.widget('mage.priceOptions', {
        options: globalOptions,

        /**
         * @private
         */
        _init: function initPriceBundle()
        {
            $(this.options.optionsSelector, this.element).trigger('change');
            if (this.options.priceChargedPer) {
                var productId = this.options.productId;
                var format = this.options.priceFormat;
                var priceText = this.options.priceChargedPerTxt;
                var priceChargedPer = utils.formatPrice(this.options.priceChargedPer, format);
                utils.formatPrice(this.options.priceChargedPer, format);
                $('#product-price-'+productId).find('.price').text(priceChargedPer);
                if (this.options.deafultFlag) {
                    $('#product-price-'+productId).after('<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">'+priceText+'</span>');
                    $('#'+this.options.optionValFieldId).trigger('click');
                    this.options.deafultFlag = 0;
                }
            }
        },

        /**
         * Widget creating method.
         * Triggered once.
         * @private
         */
        _create: function createPriceOptions()
        {
            var form = this.element,
                options = $(this.options.optionsSelector, form),
                priceBox = $(this.options.priceHolderSelector, $(this.options.optionsSelector).element);

            if (priceBox.data('magePriceBox') &&
                priceBox.priceBox('option') &&
                priceBox.priceBox('option').priceConfig
            ) {
                if (priceBox.priceBox('option').priceConfig.optionTemplate) {
                    this._setOption('optionTemplate', priceBox.priceBox('option').priceConfig.optionTemplate);
                }
                this._setOption('priceFormat', priceBox.priceBox('option').priceConfig.priceFormat);
            }

            options.on('change', this._onOptionChanged.bind(this));
        },

        /**
         * Custom option change-event handler
         * @param {Event} event
         * @private
         */
        _onOptionChanged: function onOptionChanged(event)
        {
            var changes,
                option = $(event.target),
                handler = this.options.optionHandlers[option.data('role')];

            option.data('optionContainer', option.closest(this.options.controlContainer));

            if (handler && handler instanceof Function ) {
            changes = handler(
                option,
                this.options.optionConfig,
                this
            );
            } else {
                changes = defaultGetOptionValue(option, this.options.optionConfig);
            }
            $(this.options.priceHolderSelector).trigger('updatePrice', changes);
            if (!this.options.deafultFlag) {
                var productId = this.options.productId;
                var priceChargedPerTxtArr = this.options.priceChargedPerTxtArr;
                var optionValId = event.currentTarget.value;
                var priceText = priceChargedPerTxtArr[optionValId];
                if (!$('#wk-bk-price-range-txt').length) {
                    $('#product-price-'+productId).after('<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">'+priceText+'</span>');
                } else {
                    $('#wk-bk-price-range-txt').text(priceText);
                }
            }
        }
    });

    return $.mage.priceOptions;
});
