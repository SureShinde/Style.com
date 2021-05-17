/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'Magento_Ui/js/modal/alert',
    'mage/template',
    "mage/calendar"
], function ($, alert, mageTemplate) {
    'use strict';
    $.widget(
        'tablebooking.tablebooking',
        {
            options: {
            },
            _create: function () {
                var self = this;
                var slotType1Index = 0;
                var slotType2Index = 0;
                var slotType3Index = 0;
                var slotType4Index = 0;
                var slotType5Index = 0;
                var slotType6Index = 0;
                var slotType7Index = 0;
                var isSavedDataAdded = {1:0, 2:0, 3:0, 4:0, 5:0, 6:0, 7:0};
                var slotDataType = parseInt(self.options.slotDataType);
                var slotData = JSON.parse(self.options.slotData);
                var currentSlotType = '';

                if (slotDataType) {
                    $('#wk-slot-all-days').trigger('click');
                }
                $('#wk-slot-all-days').each(function () {
                    if ($(this).prop('checked')) {
                        var progressTmpl = mageTemplate('#wk-booking-same-slot-container-template'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        currentSlotType = 1;
                    } else {
                        var progressTmpl = mageTemplate('#wk-booking-slot-container-template'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        currentSlotType = 0;
                    }
                    $('#wk-slot-for-all-days-wrapper').after(tmpl);
                });

                $('body').on('change', '#wk-slot-all-days', function () {
                    isSavedDataAdded = {1:0, 2:0, 3:0, 4:0, 5:0, 6:0, 7:0};
                    slotType1Index = slotType2Index = slotType3Index = slotType4Index = slotType5Index = 0;
                    slotType6Index = slotType7Index = 0;
                    $('.wk-booking-slot-row').remove();
                    if ($(this).prop('checked')) {
                        var progressTmpl = mageTemplate('#wk-booking-same-slot-container-template'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        currentSlotType = 1;
                    } else {
                        var progressTmpl = mageTemplate('#wk-booking-slot-container-template'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        currentSlotType = 0;
                    }
                    $('#wk-slot-for-all-days-wrapper').after(tmpl);
                    $('.wk-booking-row-add-btn').trigger('click', ["auto"]);
                });

                $('body').on('click', '.wk-booking-row-add-btn', function (event, type) {
                    var slotTypeId = $(this).attr('data-day-type');
                    var isNewRow = 1;
                    var slotDataNotAvl = false;
                    if (!isSavedDataAdded[slotTypeId] && currentSlotType==slotDataType) {
                        if (slotData[slotTypeId]) {
                            $.each(slotData[slotTypeId], function (dayindex, dayvalue) {
                                var index = slotType1Index;
                                if (slotTypeId == 1) {
                                    slotType1Index = slotType1Index + 1;
                                }
                                if (slotTypeId == 2) {
                                    index = slotType2Index;
                                    slotType2Index = slotType2Index + 1;
                                }
                                if (slotTypeId == 3) {
                                    index = slotType3Index;
                                    slotType3Index = slotType3Index + 1;
                                }
                                if (slotTypeId == 4) {
                                    index = slotType4Index;
                                    slotType4Index = slotType4Index + 1;
                                }
                                if (slotTypeId == 5) {
                                    index = slotType5Index;
                                    slotType5Index = slotType5Index + 1;
                                }
                                if (slotTypeId == 6) {
                                    index = slotType6Index;
                                    slotType6Index = slotType6Index + 1;
                                }
                                if (slotTypeId == 7) {
                                    index = slotType7Index;
                                    slotType7Index = slotType7Index + 1;
                                }
                                var progressTmpl = mageTemplate('#wk-booking-slot-template'),
                                        tmpl;
                                tmpl = progressTmpl({
                                    data: {
                                        id: slotTypeId,
                                        index: index,
                                        from: dayvalue.from,
                                        to: dayvalue.to,
                                        qty: dayvalue.qty,
                                    }
                                });
                                $('#wk-booking-slot-container'+slotTypeId).append(tmpl);
                                $(".wk-booking-slot-picker").timepicker({
                                    timeFormat: 'hh:mm tt',
                                    'showButtonPanel': true,
                                    'showOn': "button",
                                    'buttonImage': null,
                                    'buttonImageOnly': null,
                                    'buttonText': ''
                                });
                                // for label row slot box
                                var progressTmpl = mageTemplate('#wk-booking-label-slot-template'),
                                tmpl;
                                tmpl = progressTmpl({
                                    data: {
                                        id: slotTypeId,
                                        index: index,
                                        from: dayvalue.from,
                                        to: dayvalue.to,
                                        qty: dayvalue.qty
                                    }
                                });
                                $('#wk-bk-label-slot-box-container'+slotTypeId).append(tmpl);
                                isSavedDataAdded[slotTypeId] = 1;
                                isNewRow = 0;
                            });
                        } else if (slotTypeId == 1 && slotDataType==0 && type=="auto") {
                            slotDataNotAvl = true;
                        }
                    }
                    if ((type!='auto' && isNewRow) || (type=='auto' && slotTypeId == 1 && isNewRow)) {
                        var index = slotType1Index;
                        if (slotTypeId == 1) {
                            slotType1Index = slotType1Index + 1;
                        }
                        if (slotTypeId == 2) {
                            index = slotType2Index;
                            slotType2Index = slotType2Index + 1;
                        }
                        if (slotTypeId == 3) {
                            index = slotType3Index;
                            slotType3Index = slotType3Index + 1;
                        }
                        if (slotTypeId == 4) {
                            index = slotType4Index;
                            slotType4Index = slotType4Index + 1;
                        }
                        if (slotTypeId == 5) {
                            index = slotType5Index;
                            slotType5Index = slotType5Index + 1;
                        }
                        if (slotTypeId == 6) {
                            index = slotType6Index;
                            slotType6Index = slotType6Index + 1;
                        }
                        if (slotTypeId == 7) {
                            index = slotType7Index;
                            slotType7Index = slotType7Index + 1;
                        }
                        
                        if (!slotDataNotAvl) {
                            var progressTmpl = mageTemplate('#wk-booking-slot-template'),
                                        tmpl;
                            tmpl = progressTmpl({
                                data: {
                                    id: slotTypeId,
                                    index: index
                                }
                            });
                            $('#wk-booking-slot-container'+slotTypeId).append(tmpl);
                            $(".wk-booking-slot-picker").timepicker({
                                timeFormat: 'hh:mm tt',
                                'showButtonPanel': true,
                                'showOn': "button",
                                'buttonImage': null,
                                'buttonImageOnly': null,
                                'buttonText': ''
                            });
                        }
                    }
                    if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container'+slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    } else {
                        $('#wk-booking-slot-container'+slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .removeClass('wk-booking-slot-closed');
                    }
                });

                $('body').on('click', '.wk-booking-row-remove-btn', function () {
                    var slotTypeId = $(this)
                        .parents('.wk-booking-slot-row')
                        .find('.wk-booking-row-add-btn')
                        .attr('data-day-type');

                    var id = $(this).parents('.wk-booking-slot-block').attr('id');
                    var idArr = id.split('wk-booking-slot-block');
                    var index = idArr[1];
                    $('#wk-bk-label-slot-box'+index).remove();

                    $(this).parents('.wk-booking-slot-block').remove();
                    if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container'+slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    }
                });

                $('.wk-booking-row-add-btn').trigger('click', ["auto"]);

                $(".wk-booking-slot-picker").timepicker({
                    timeFormat: 'hh:mm tt',
                    'showButtonPanel': true,
                    'showOn': "button",
                    'buttonImage': null,
                    'buttonImageOnly': null,
                    'buttonText': ''
                });

                $('body').on('click', '.wk-booking-slot-container-label', function () {
                    $(this).parents('.admin__field').find('.admin__collapsible-content').toggle(
                        "fast",
                        function () {
                            if ($(this).hasClass('_show')) {
                                $(this).addClass('wk-bk-hide');
                                $(this).removeClass('_show');
                                $(this).parents('.wk-booking-slot-row').removeClass('_show');
                                $(this)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-bk-label-slot-box-container')
                                .removeClass('wk-bk-hide')
                                .addClass('_show');
                            } else {
                                $(this).removeClass('wk-bk-hide');
                                $(this).addClass('_show');
                                $(this).parents('.wk-booking-slot-row').addClass('_show');
                                $(this)
                                .parents('.wk-booking-slot-row')
                                .find('.wk-bk-label-slot-box-container')
                                .removeClass('_show')
                                .addClass('wk-bk-hide');
                            }
                        }
                    );
                });

                $('body').on('click', '.wk-bk-label-slot-box-close', function () {
                    var slotTypeId = $(this)
                        .parents('.wk-booking-slot-row')
                        .find('.wk-booking-row-add-btn')
                        .attr('data-day-type');
                    var id = $(this).parents('.wk-bk-label-slot-box').attr('id');
                    var idArr = id.split('wk-bk-label-slot-box');
                    var index = idArr[1];
                    $(this).parents('.wk-bk-label-slot-box').remove();
                    $('#wk-booking-slot-block'+index).remove();
                    if (!$('#wk-booking-slot-container'+slotTypeId).find('.wk-booking-slot-block').length) {
                        $('#wk-booking-slot-container'+slotTypeId)
                            .parents('.wk-booking-slot-row')
                            .find('.wk-booking-slot-container-label')
                            .addClass('wk-booking-slot-closed');
                    }
                });

                if ($("#wk-booking-price_charged_per_table").find(":selected").val() == 2) {
                    $("#wk-guests_per_table").show().addClass("required");
                    $("#wk-guests_per_table input#wk-no_of_guests").addClass("required-entry");
                } else {
                    $("#wk-guests_per_table").hide().removeClass("required");
                    $("#wk-guests_per_table input#wk-no_of_guests").removeClass("required-entry");
                }
                
                $('body').on('change', '#wk-booking-price_charged_per_table', function () {
                    if ($(this).find(":selected").val() == 2) {
                        $("#wk-guests_per_table").show().addClass("required");
                        $("#wk-guests_per_table input#wk-no_of_guests").addClass("required-entry");
                    } else {
                        $("#wk-guests_per_table").hide().removeClass("required");
                        $("#wk-guests_per_table input#wk-no_of_guests").removeClass("required-entry");
                    }

                });
            }
        }
    );
    return $.tablebooking.tablebooking;
});
