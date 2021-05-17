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
        eventOptionsSelector: '.wk-event-custom-option',
        optionConfig: {},
        optionHandlers: {},
        controlContainer: 'dd'
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
            $(this.options.eventOptionsSelector, this.element).trigger('change');
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
                eventOptions = $(this.options.eventOptionsSelector, form),
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
            eventOptions.on('change', this._onEventOptionChanged.bind(this));
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
            if (this.options.priceRangeFrom && this.options.priceRangeTo) {
                var productId = this.options.productId;
                var format = this.options.priceFormat;
                var priceText = this.options.priceRangeText;
                var priceFrom = utils.formatPrice(this.options.priceRangeFrom, format);
                utils.formatPrice(this.options.priceRangeFrom, format);
                if (this.options.priceRangeTo == this.options.priceRangeFrom) {
                    $('#product-price-' + productId).find('.price').text(priceFrom);
                } else {
                    var priceTo = utils.formatPrice(this.options.priceRangeTo, format);
                    $('#product-price-' + productId).find('.price').text(priceFrom + ' - ' + priceTo);
                }
                if (!$('#wk-bk-price-range-txt').length) {
                    $('#product-price-' + productId).after('<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">' + priceText + '</span>');
                }
            }
        },

        /**
         * Custom event option change-event handler
         * @param {Event} event
         * @private
         */
        _onEventOptionChanged: function onEventOptionChanged(event)
        {
            var option = $(event.target);
            var optId = option.attr('data-option-id');
            var optValId = option.attr('data-option-val-id');
            if (option.val()) {
                if (!$('#select_' + optId + optValId).length) {
                    $('.wk-bk-options-wrapper').append('<input type="hidden" name="options[' + optId + '][]" value="' + optValId + '" id="select_' + optId + optValId + '"/>');
                }
            } else {
                if ($('#select_' + optId + optValId).length) {
                    $('#select_' + optId + optValId).remove();
                }
            }
            var totalQty = 0;
            var totalPrice = 0;
            var eventOptionPrice = this.options.eventOptionConfig;
            var basePrice = parseFloat(this.options.productBasePrice);
            console.log(basePrice);
            $('.wk-event-custom-option').each(function () {
                if ($(this).val()) {
                    totalQty = totalQty + parseFloat($(this).val());
                    var cOptionId = $(this).attr('data-option-id');
                    var cOptionValue = $(this).attr('data-option-val-id');
                    var cOptionPriceArr = eventOptionPrice[cOptionId + '_' + cOptionValue];
                    totalPrice = totalPrice + ((parseFloat(cOptionPriceArr.basePrice) + parseFloat(basePrice)) * parseFloat($(this).val()));
                    var cOptionPriceArr = eventOptionPrice[cOptionId + '_' + cOptionValue];
                }
            });
            $('#wk-bk-event-total-qty').text(totalQty);
            var productId = this.options.productId;
            var format = this.options.priceFormat;
            if (!totalPrice) {
                totalPrice = basePrice;
            }
            $('#product-total-price-' + productId).find('.price').text(
                utils.formatPrice(totalPrice, format)
            );
            if (this.options.priceRangeFrom && this.options.priceRangeTo) {
                var priceText = this.options.priceRangeText;
                var priceFrom = utils.formatPrice(this.options.priceRangeFrom, format);
                utils.formatPrice(this.options.priceRangeFrom, format);
                if (this.options.priceRangeTo == this.options.priceRangeFrom) {
                    $('#product-price-' + productId).find('.price').text(priceFrom);
                } else {
                    var priceTo = utils.formatPrice(this.options.priceRangeTo, format);
                    $('#product-price-' + productId).find('.price').text(priceFrom + ' - ' + priceTo);
                }
                if (!$('#wk-bk-price-range-txt').length) {
                    $('#product-price-' + productId).after('<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">' + priceText + '</span>');
                }
            }
        }
    });

    return $.mage.priceOptions;
});