/**
 * Checkout/cart gift card logic
 */
define([
    'jquery',
    'uiComponent',
    'Amasty_GiftCardAccount/js/model/payment/gift-card-messages',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/translate',
    'Amasty_GiftCardAccount/js/action/loader',
    'Magento_Checkout/js/model/error-processor',
    'Amasty_GiftCardAccount/js/action/gift-code-actions'
], function ($, Component, messageContainer, total, getPaymentInformationAction, fullScreenLoader, $t,
    loader, errorProcessor, giftCodeActions) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_GiftCardAccount/payment/gift-card',
            cardCode: '',
            applyCodes: '',
            loader: {},
            isCart: false,
            emptyFieldText: $t('Enter Gift Card Code'),
            wrongCodeText: $t('Wrong Gift Card Code.'),
            links: {
                checkedCards: '${ "amcard-cart-render" }:cards'
            }
        },

        initialize: function () {
            this._super();
            var codes;

            if (total.getSegment('amasty_giftcard')) {
                codes = total.getSegment('amasty_giftcard').title.replace(' ', '');
                this.applyCodes(codes);
            }

            if (!this.applyCodes()) {
                this.applyCodes('');
            }

            this.loader = loader(this.isCart);

            return this;
        },

        initObservable: function () {
            this._super()
                .observe(['cardCode', 'checkedCards', 'applyCodes']);

            return this;
        },

        /**
         * Gift code remove
         */
        removeSelected: function (cartCode) {
            this.loader.start();

            giftCodeActions.remove(cartCode)
                .done(function (code) {
                    this.removeDone(code);
                }.bind(this))
                .fail(function (response) {
                    total.isLoading(false);
                    this.loader.stop();
                    errorProcessor.process(response, messageContainer);
                }.bind(this));
        },

        removeDone: function (code) {
            var deferred = $.Deferred(),
                appliedCodes = this.applyCodes().split(','),
                message = $t('Gift Card %1 was removed.').replace('%1', code);

            if (appliedCodes.indexOf(code) !== -1) {
                appliedCodes.splice(appliedCodes.indexOf(code), 1);
            }

            total.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                this.applyCodes(appliedCodes.join(','));
                total.isLoading(false);
                this.loader.stop();
            }.bind(this));

            messageContainer.addSuccessMessage({
                'message': message
            });
        },

        /**
         * Gift card code code application procedure
         */
        apply: function () {
            if (!this.validate()) {
                return;
            }

            this.loader.start();

            giftCodeActions.set(this.cardCode()).done(function (newCode) {
                if (newCode) {
                    this.applyDone(newCode);
                }
            }.bind(this))
                .fail(function (response) {
                    this.loader.stop();
                    total.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }.bind(this));
        },

        applyDone: function (newCode) {
            var deferred,
                appliedCodes = this.applyCodes().split(','),
                message = $t('Gift Card "%1" was added.').replace('%1', newCode);

            deferred = $.Deferred();
            total.isLoading(true);
            getPaymentInformationAction(deferred);

            $.when(deferred).done(function () {
                appliedCodes.push(newCode);
                this.applyCodes(appliedCodes.join(','));
                this.loader.stop();
                total.isLoading(false);
                this.cardCode('');
            }.bind(this));

            messageContainer.addSuccessMessage({
                'message': message
            });
        },

        /**
         * Check gift card code
         */
        check: function () {
            if (!this.validate()) {
                return;
            }

            this.loader.start();
            giftCodeActions.check(this.cardCode()).done(function (response) {
                this.loader.stop();

                if (!response.length) {
                    messageContainer.addErrorMessage({
                        'message': this.wrongCodeText
                    });

                    return;
                }

                this.checkedCards(JSON.parse(response));
            }.bind(this));
        },

        validate: function () {
            if (this.cardCode()) {
                return true;
            }

            messageContainer.addErrorMessage({
                'message': $t(this.emptyFieldText)
            });

            return false;
        },

        isGiftCardEnable: function () {
            return window.checkoutConfig.isGiftCardEnabled;
        }
    });
});
