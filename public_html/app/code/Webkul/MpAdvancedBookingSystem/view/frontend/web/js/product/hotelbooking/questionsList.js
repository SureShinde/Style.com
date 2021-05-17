/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define([
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, mageTemplate, alert) {
    'use strict';
    $.widget('mage.questionsList', {
        options: {
            backUrl: ''
        },
        _create: function () {
            var self = this;
            var indexValue = 0;
            var questionProductData = $.parseJSON(self.options.questionProducts);
            if ($.isArray(questionProductData)) {
                $(document).ajaxComplete(function ( event, request, settings ) {
                    var responseData = $.parseJSON(request.responseText);
                    var currentAjaxUrl = settings.url;
                    if (currentAjaxUrl.indexOf("mpadvancebooking_questions_product_listing") && responseData.totalRecords>0) {
                        setTimeout(function () {
                            if ($('#question-product-block-wrapper .data-row').length) {
                                questionProductData.each(function (index, value) {
                                    var indexId = index;
                                    $("#questionIdscheck"+indexId).trigger("click");
                                    questionProductData = $.grep(questionProductData, function (arrValue) {
                                      return indexId !== arrValue;
                                    });
                                });
                                $("#question-product-block-loader").hide();
                                $("#question-product-block-wrapper").show();
                            } else {
                                setTimeout(function () {
                                    if ($('#question-product-block-wrapper .data-row').length) {
                                        questionProductData.each(function (index, value) {
                                            var indexId = index;
                                            $("#questionIdscheck"+indexId).trigger("click");
                                        });
                                        $("#question-product-block-loader").hide();
                                        $("#question-product-block-wrapper").show();
                                    } else {
                                        $("#question-product-block-loader").hide();
                                        $("#question-product-block-wrapper").show();
                                    }
                                }, 2000);
                            }
                        }, 2000);
                    } else {
                        $("#question-product-block-loader").hide();
                        $("#question-product-block-wrapper").show();
                    }
                });
            }
        }
    });
    return $.mage.questionsList;
});
