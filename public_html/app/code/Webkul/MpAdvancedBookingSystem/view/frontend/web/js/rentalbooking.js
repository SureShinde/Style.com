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
    "moment",
    "mage/calendar"
], function ($, alert, mageTemplate, moment) {
    'use strict';
    $.widget(
        'rentalbooking.rentalbooking',
        {
            options: {
            },
            _create: function () {
                var self = this;
                var monthArr = self.options.monthArr;
                var rentTypeArr = self.options.rentTypeArr;
                var unvailableDates = self.options.hourlyUnavailableDates;
                var dateFormat = "mm/dd/yy";
                $('.wk-bk-rent-type-option').each(function () {
                    if (this.checked) {
                        $('#wk-bk-select-slot').html('');
                        $('#wk-bk-select-time').html('');
                        if (parseInt($(this).val()) == parseInt(rentTypeArr['d'])) {
                            var progressTmpl = mageTemplate('#wk-bk-select-date-template-daily'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-select-date').html(tmpl);
                            var bookingDateFrom = $("#wk-bk-select-from-date")
                                .datepicker({
                                    minDate: self.options.bookingAvailableFrom,
                                    maxDate: self.options.bookingAvailableTo
                                })
                                .on("change", function () {
                                    bookingDateTo.datepicker("option", "minDate", getBookingDate(this));
                                }),
                            bookingDateTo = $("#wk-bk-select-to-date")
                                .datepicker({
                                    minDate: self.options.bookingAvailableFrom,
                                    maxDate: self.options.bookingAvailableTo
                                })
                                .on("change", function () {
                                    bookingDateFrom.datepicker("option", "maxDate", getBookingDate(this));
                                });
                        } else {
                            var progressTmpl = mageTemplate('#wk-bk-select-date-template-hourly'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-select-date').html(tmpl);
                            var bookingDateFrom = $("#wk-bk-select-from-date")
                            .datepicker({
                                minDate: self.options.bookingAvailableFrom,
                                maxDate: self.options.bookingAvailableTo,
                                beforeShowDay: function (date) {
                                    var string = $.datepicker.formatDate('dd M, yy', date);
                                    var isDisabled = ($.inArray(string, unvailableDates) != -1);
                                    return [!isDisabled];
                                },
                                onSelect: function () {
                                    getBookingDate(this);
                                }
                            });
                        }
                    }
                });
           
                function getBookingDate(element)
                {
                    var date;
                    try {
                        date = $.datepicker.parseDate(dateFormat, element.value);
                        var selectedDate  = $(element).val();
                        var selectedDateArr  = selectedDate.split('/');
                        var day = selectedDateArr[1];
                        var month = selectedDateArr[0];
                        var year = selectedDateArr[2];
                        var formatedDate = day+" "+monthArr[month]+", "+year;
                        var d = new Date(selectedDate);
                        var dayIndex = d.getDay();
                        if (!dayIndex) {
                            dayIndex = 7;
                        }

                        $(element)
                            .parents('.wk-bk-dates-wrapper')
                            .find('.wk-bk-select-date-label').text(formatedDate);

                        var rentType = $(".wk-bk-rent-type-option:input[type='radio']:checked").val();
                        if (parseInt(rentTypeArr['d']) == parseInt(rentType)) {
                            $(element)
                                .parents('.wk-bk-dates-wrapper')
                                .find('.wk-bk-date-option').val(formatedDate);
                        } else {
                            if ($('#wk-bk-select-from-date').val()) {
                                var fromDate = new Date($('#wk-bk-select-from-date').val());
                                var fday = fromDate.getDate();
                                var fmonth = fromDate.getMonth() + 1;
                                var fyear = fromDate.getFullYear();

                                var todayDate = new Date();
                                var tday = todayDate.getDate();
                                var tmonth = todayDate.getMonth() + 1;
                                var tyear = todayDate.getFullYear();
                                // if from selected date is today
                                if (fday == tday && fmonth == tmonth && fyear == tyear) {
                                    var progressTmpl = mageTemplate('#wk-bk-select-slot-template-today'),
                                    tmpl;
                                    tmpl = progressTmpl({
                                        data: {}
                                    });
                                } else {
                                    var progressTmpl = mageTemplate('#wk-bk-select-slot-template-'+dayIndex),
                                    tmpl;
                                    tmpl = progressTmpl({
                                        data: {}
                                    });
                                }
                                $('#wk-bk-select-slot').html(tmpl);
                            } else {
                                $('#wk-bk-select-slot').html('');
                            }
                            $('#wk-bk-select-time').html('');
                        }
                    } catch (error) {
                        date = null;
                    }
                    return date;
                }
                $("body").on('change', '.wk-bk-rent-type-option', function () {
                    if (this.checked) {
                        $('#wk-bk-select-slot').html('');
                        $('#wk-bk-select-time').html('');
                        if (parseInt($(this).val()) == parseInt(rentTypeArr['d'])) {
                            var progressTmpl = mageTemplate('#wk-bk-select-date-template-daily'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-select-date').html(tmpl);
                            var bookingDateFrom = $("#wk-bk-select-from-date")
                                .datepicker({
                                    minDate: self.options.bookingAvailableFrom,
                                    maxDate: self.options.bookingAvailableTo
                                })
                                .on("change", function () {
                                    bookingDateTo.datepicker("option", "minDate", getBookingDate(this));
                                }),
                            bookingDateTo = $("#wk-bk-select-to-date")
                                .datepicker({
                                    minDate: self.options.bookingAvailableFrom,
                                    maxDate: self.options.bookingAvailableTo
                                })
                                .on("change", function () {
                                    bookingDateFrom.datepicker("option", "maxDate", getBookingDate(this));
                                });
                        } else {
                            var progressTmpl = mageTemplate('#wk-bk-select-date-template-hourly'),
                            tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-select-date').html(tmpl);
                            var bookingDateFrom = $("#wk-bk-select-from-date")
                            .datepicker({
                                minDate: self.options.bookingAvailableFrom,
                                maxDate: self.options.bookingAvailableTo,
                                beforeShowDay: function (date) {
                                    var string = $.datepicker.formatDate('dd M, yy', date);
                                    var isDisabled = ($.inArray(string, unvailableDates) != -1);
                                    return [!isDisabled];
                                },
                                onSelect: function () {
                                    getBookingDate(this);
                                }
                            });
                        }
                    }
                });
                $("body").on('change', '#wk-bk-parent-slot-id', function () {
                    $('#wk-bk-from-date-option').val('');
                    $('#wk-bk-to-date-option').val('');
                    if ($(this).val()) {
                        var dayIndex = $('#wk-bk-slot-day-index').val();
                        var slotIndex = $(this).val();
                        var progressTmpl = mageTemplate('#wk-bk-select-time-template'),
                        tmpl;
                        tmpl = progressTmpl({
                            data: {}
                        });
                        $('#wk-bk-select-time').html(tmpl);

                        var formattedDate = moment($('#wk-bk-select-from-date').val()).format('YYYY-MM-DD');
                        var fromDate = new Date(formattedDate);
                        var fday = fromDate.getDate();
                        var fmonth = fromDate.getMonth() + 1;
                        var fyear = fromDate.getFullYear();

                        var todayDate = new Date();
                        var tday = todayDate.getDate();
                        var tmonth = todayDate.getMonth() + 1;
                        var tyear = todayDate.getFullYear();
                        // if from selected date is today
                        if (fday == tday && fmonth == tmonth && fyear == tyear) {
                            var progressTmpl = mageTemplate(
                                '#wk-bk-select-from-time-opt-template-today'+dayIndex+slotIndex
                            ), tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-from-time').html(progressTmpl);
    
                            var progressTmpl = mageTemplate(
                                '#wk-bk-select-to-time-opt-template-today'+dayIndex+slotIndex
                            ), tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-to-time').html(progressTmpl);
                        } else {
                            var progressTmpl = mageTemplate(
                                '#wk-bk-select-from-time-opt-template'+dayIndex+slotIndex
                            ), tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-from-time').html(progressTmpl);
    
                            var progressTmpl = mageTemplate(
                                '#wk-bk-select-to-time-opt-template'+dayIndex+slotIndex
                            ), tmpl;
                            tmpl = progressTmpl({
                                data: {}
                            });
                            $('#wk-bk-to-time').html(progressTmpl);
                        }

                        $('#wk-bk-to-time').attr('disabled', 'disabled');
                    } else {
                        $('#wk-bk-select-time').html('');
                    }
                });
                $("body").on('change', '#wk-bk-from-time', function () {
                    var thisObj = $(this);
                    $('#wk-bk-to-time').removeAttr('disabled');
                    $("#wk-bk-from-time option").each(function () {
                        this.disabled = false;
                    });
                    $("#wk-bk-to-time option").each(function () {
                        this.disabled = false;
                    });
                    if (thisObj.val()) {
                        $('#wk-bk-slot-id').val(thisObj.val());
                        if (thisObj.val() && $('#wk-bk-to-time').val()) {
                            if (parseInt(thisObj.val()) >= parseInt($('#wk-bk-to-time').val())) {
                                thisObj.val('');
                                $('#wk-bk-from-date-option').val('');
                            } else {
                                var currentDate = $('#wk-bk-select-from-date')
                                .parents('.wk-bk-dates-wrapper')
                                .find('.wk-bk-select-date-label').text();
                                var currentFromDate = currentDate;
                                var currentToDate = currentDate;
                                var currentFromTime = $("#wk-bk-from-time option:selected").text();
                                if (currentFromTime && currentFromTime!=0) {
                                    currentFromDate = currentDate+' '+currentFromTime;
                                }
                                $('#wk-bk-from-date-option').val(currentFromDate);
                                var currentToTime = $("#wk-bk-to-time option:selected").text();
                                if (currentToTime && currentToTime!=0) {
                                    currentToDate = currentDate+' '+currentToTime;
                                }
                                $('#wk-bk-to-date-option').val(currentToDate);
                            }
                        }
                        $("#wk-bk-to-time option").each(function () {
                            if (parseInt(this.value) <= parseInt(thisObj.val())) {
                                this.disabled = true;
                            }
                        });
                    } else {
                        $('#wk-bk-slot-id').val('');
                        $('#wk-bk-to-time').val('');
                        $('#wk-bk-to-date-option').val('');
                        $('#wk-bk-to-time').attr('disabled', 'disabled');
                    }
                });
                $("body").on('change', '#wk-bk-to-time', function () {
                    var thisObj = $(this);
                    $('#wk-bk-to-time').removeAttr('disabled');
                    $("#wk-bk-from-time option").each(function () {
                        this.disabled = false;
                    });
                    $("#wk-bk-to-time option").each(function () {
                        this.disabled = false;
                    });
                    if (thisObj.val() && $('#wk-bk-from-time').val()) {
                        if (parseInt(thisObj.val()) <= parseInt($('#wk-bk-from-time').val())) {
                            thisObj.val('');
                            $('#wk-bk-to-date-option').val('');
                        } else {
                            var currentDate = $('#wk-bk-select-from-date')
                            .parents('.wk-bk-dates-wrapper')
                            .find('.wk-bk-select-date-label').text();
                            var currentFromDate = currentDate;
                            var currentToDate = currentDate;
                            var currentFromTime = $("#wk-bk-from-time option:selected").text();
                            if (currentFromTime && currentFromTime!=0) {
                                currentFromDate = currentDate+' '+currentFromTime;
                            }
                            $('#wk-bk-from-date-option').val(currentFromDate);
                            var currentToTime = $("#wk-bk-to-time option:selected").text();
                            if (currentToTime && currentToTime!=0) {
                                currentToDate = currentDate+' '+currentToTime;
                            }
                            $('#wk-bk-to-date-option').val(currentToDate);
                        }
                        $("#wk-bk-from-time option").each(function () {
                            if (parseInt(this.value) >= parseInt(thisObj.val())) {
                                this.disabled = true;
                            }
                        });
                    } else {
                        $('#wk-bk-slot-id').val('');
                        $('#wk-bk-to-time').val('');
                        $('#wk-bk-to-date-option').val('');
                        if (!$('#wk-bk-from-time').val()) {
                            $('#wk-bk-to-time').attr('disabled', 'disabled');
                        }
                    }
                });
            }
        }
    );
    return $.rentalbooking.rentalbooking;
});
