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
                var monthArr = self.options.monthArr;
                var options = JSON.parse(self.options.optionsJson);
                $("#wk-bk-select-date").calendar({
                    'dateFormat':'mm/dd/yy',
                    'minDate': self.options.bookingAvailableFrom,
                    'maxDate': self.options.bookingAvailableTo
                });
                var progressTmpl = mageTemplate('#wk-bk-select-time-opt-template-today'),
                tmpl;
                tmpl = progressTmpl({
                    data: {}
                });
                $('.wk-bk-select-time-opt-wrapper').html(tmpl);
                if (self.options.priceRangeText.length) {
                    $('#product-price-'+self.options.productId).after(
                        '<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">'+self.options.priceRangeText+'</span>'
                    );
                }

                $("body").on('change', '#wk-bk-select-date', function () {
                    var selectedDate  = $(this).val();

                    var parsedDate = Date.parse(selectedDate, "yyyy-MM-dd");
                    var str = parsedDate.toString();
                    parsedDate = str.substr(0, str.length-3);

                    var selectedDateArr  = selectedDate.split('/');
                    var day = selectedDateArr[1];
                    var month = selectedDateArr[0];
                    var year = selectedDateArr[2];
                    var formatedDate = day+" "+monthArr[month]+", "+year;
                    var d = new Date(selectedDate);
                    var dayIndex = d.getDay();
                    if (dayIndex == 0) {
                        dayIndex = 7;
                    }

                    var todayDate = new Date();
                    var tday = todayDate.getDate();
                    var tmonth = todayDate.getMonth() + 1;
                    var tyear = todayDate.getFullYear();
                    // if from selected date is today
                    if (day == tday && month == tmonth && year == tyear) {
                        var progressTmpl = mageTemplate('#wk-bk-select-time-opt-template-today'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                    } else {
                        var progressTmpl = mageTemplate('#wk-bk-select-time-opt-template'+dayIndex),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                    }

                    $('.wk-bk-select-time-opt-wrapper').html(tmpl);

                    if (parsedDate in self.options.bookedData) {
                        $.each(self.options.bookedData[parsedDate], function (key, value) {
                            if ($('.wk-bk-select-time-opt-wrapper').find(".wk-bk-select-time-opt[data-type-time='"+key+"']").length) {
                                var optTimeHtml = $('.wk-bk-select-time-opt-wrapper').find(".wk-bk-select-time-opt[data-type-time='"+key+"']");
                                var remainingQty = optTimeHtml.data("slot-qty") - value;
                                if (remainingQty <= 0) {
                                    optTimeHtml.removeClass('wk-bk-slot-booked');
                                    optTimeHtml.removeClass('wk-bk-slot-selected');
                                    optTimeHtml.addClass('wk-bk-slot-booked');
                                    $('.wk-bk-select-time-opt-wrapper').find(".wk-bk-select-time-opt[data-type-time='"+key+"']").next().addClass('wk-bk-slot-selected');
                                }
                            }
                        });
                    }
                    
                    $('.wk-bk-select-time-opt-wrapper').removeAttr('style');
                    var selectedTime = $('.wk-bk-slot-selected').text();
                    var isTimeClosed = false;
                    $('#wk-bk-booking-time-field').val(selectedTime);
                    $('#wk-bk-slot-day-index').val($('.wk-bk-slot-selected').attr('data-day-index'));
                    $('#wk-bk-parent-slot-id').val($('.wk-bk-slot-selected').attr('data-index'));
                    $('#wk-bk-slot-id').val($('.wk-bk-slot-selected').attr('data-slot-index'));
                    if (!tmpl) {
                        selectedTime = self.options.titleClosed;
                        isTimeClosed = true;
                        $('.wk-bk-select-time-opt-wrapper').attr('style', 'display: none;');
                    }
                    if (selectedTime.length == 0) {
                        selectedTime = self.options.titleClosed;
                        isTimeClosed = true;
                    }
                    $(this).parents('.wk-bk-dates-container').find('.wk-bk-select-time-label').text(selectedTime);
                    $('.wk-bk-select-date-label').text(formatedDate);
                    var bookedDate = $('.wk-bk-select-date-label').text();
                    $.each(options, function (k, v) {
                        if (v.title == 'Booking Date') {
                            if ($('#options_'+v.id+'_text').length) {
                                $('#options_'+v.id+'_text').val(bookedDate);
                            } else {
                                $('#product-options-wrapper').find('.fieldset').append(
                                    $('<input/>')
                                        .attr('type', 'hidden')
                                        .attr('name', 'options['+v.id+']')
                                        .attr('id', 'options_'+v.id+'_text')
                                        .val(bookedDate)
                                );
                            }
                        }
                        if (v.title == 'Booking Slot') {
                            if (isTimeClosed) {
                                selectedTime = "";
                            }
                            if ($('#options_'+v.id+'_text').length) {
                                $('#options_'+v.id+'_text').val(selectedTime);
                            } else {
                                $('#product-options-wrapper').find('.fieldset').append(
                                    $('<input/>')
                                        .attr('type', 'hidden')
                                        .attr('name', 'options['+v.id+']')
                                        .attr('id', 'options_'+v.id+'_text')
                                        .val(selectedTime)
                                );
                            }
                        }
                    });
                });
                $("body").on('click', '.wk-bk-select-time-opt', function () {
                    if (!$(this).hasClass('wk-bk-slot-booked')) {
                        var selectedTime  = $(this).attr('data-type-time');
                        var thisObj = $(this);
                        $(".wk-bk-select-time-opt").each(function () {
                            if ($(this).hasClass('wk-bk-slot-selected')) {
                                $(this).removeClass('wk-bk-slot-selected');
                            }
                        });
                        thisObj.parents('.wk-bk-dates-right-container').find('.wk-bk-select-time-label').text(selectedTime);
                        thisObj.addClass('wk-bk-slot-selected');
                        $('#wk-bk-booking-time-field').val(selectedTime);
                        $('#wk-bk-slot-day-index').val(thisObj.attr('data-day-index'));
                        $('#wk-bk-parent-slot-id').val(thisObj.attr('data-index'));
                        $('#wk-bk-slot-id').val(thisObj.attr('data-slot-index'));
                        var bookedDate = $('.wk-bk-select-date-label').text();
                        $.each(options, function (k, v) {
                            if (v.title == 'Booking Date') {
                                if ($('#options_'+v.id+'_text').length) {
                                    $('#options_'+v.id+'_text').val(bookedDate);
                                } else {
                                    $('#product-options-wrapper').find('.fieldset').append(
                                        $('<input/>')
                                            .attr('type', 'hidden')
                                            .attr('name', 'options['+v.id+']')
                                            .attr('id', 'options_'+v.id+'_text')
                                            .val(bookedDate)
                                    );
                                }
                            }
                            if (v.title == 'Booking Slot') {
                                if ($('#options_'+v.id+'_text').length) {
                                    $('#options_'+v.id+'_text').val(selectedTime);
                                } else {
                                    $('#product-options-wrapper').find('.fieldset').append(
                                        $('<input/>')
                                            .attr('type', 'hidden')
                                            .attr('name', 'options['+v.id+']')
                                            .attr('id', 'options_'+v.id+'_text')
                                            .val(selectedTime)
                                    );
                                }
                            }
                        });
                    }
                });

                $(document).on(
                    'click',
                    '.wk-book-now',
                    function (e) {
                        e.stopPropagation();
                        if ($("form#product_addtocart_form").valid()) {
                            if (self.options.chargedPerConfig==1) {
                                var selectedQty = $("form#product_addtocart_form").find("input[name='temp_qty']").val();
                                var updatedQty = 1;
                                if (self.options.stepSize > 1) {
                                    if (selectedQty % self.options.stepSize == 0) {
                                        updatedQty = selectedQty / self.options.stepSize;
                                    } else {
                                        updatedQty = selectedQty / self.options.stepSize;
                                        if (updatedQty < 1) {
                                            updatedQty = updatedQty + 1;
                                        }
                                    }
                                } else {
                                    updatedQty = selectedQty;
                                }
                                if (updatedQty > 0) {
                                    $("form#product_addtocart_form").find("input[name='qty']").val(parseInt(updatedQty));
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                return true;
                            }
                        }
                    }
                );
            }
        }
    );
    return $.tablebooking.tablebooking;
});
