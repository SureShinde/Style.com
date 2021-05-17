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
    'jquery/ui',
    'jquery/jquery-ui-timepicker-addon'
], function ($, alert, mageTemplate) {
    'use strict';
    $.widget(
        'mpeventbooking.mpeventbooking',
        {
            options: {},
            _create: function () {
                var self = this;
                var showMapLocation = parseInt(self.options.showMapLocation);
                var eventChartAvailable = parseInt(self.options.eventChartAvailable);
                var isMultipleTickets = parseInt(self.options.isMultipleTickets);
                var dateFormat = "mm/dd/yy";
                var startTimeTextBox = $("#wk-booking-event-from");
                var endTimeTextBox = $("#wk-booking-event-to");
                $.timepicker.datetimeRange(startTimeTextBox, endTimeTextBox, {
                    timeOnly: false,
                    dateFormat: 'mm/dd/yy',
                    timeFormat: 'HH:mm',
                    controlType: 'select',
                    minDate: new Date(),
                    showOn: "button",
                    buttonImage: null,
                    buttonImageOnly: null,
                    buttonText: ''
                });

                // var bookingDateFrom = $("#wk-booking-event-from").datetimepicker({
                //     'dateFormat': 'mm/dd/yy',
                //     'timeFormat': 'HH:mm',
                //     'showsTime': false,
                //     'minDate': new Date(),
                //     'storeLocale': 'en_US',
                //     'showButtonPanel': true,
                //     'showOn': "button",
                //     'buttonImage': null,
                //     'buttonImageOnly': null,
                //     'buttonText': '',
                // })
                // .on( "change", function() {
                //     bookingDateTo.datepicker("option", "minDate", getBookingDate(this));
                // });
                // var bookingDateTo = $("#wk-booking-event-to").datetimepicker({
                //     'dateFormat': 'mm/dd/yy',
                //     'timeFormat': 'HH:mm',
                //     'showsTime': true,
                //     'minDate': new Date(),
                //     'storeLocale': 'en_US',
                //     'showButtonPanel': true,
                //     'showOn': "button",
                //     'buttonImage': null,
                //     'buttonImageOnly': null,
                //     'buttonText': ''
                // })
                // .on( "change", function() {
                //     bookingDateFrom.datepicker("option", "maxDate", getBookingDate(this));
                // });

                isShowMapLocation(showMapLocation);
                isMultipleTicketsAvailable(isMultipleTickets);
                isEventChartAvailable(eventChartAvailable);

                $('body').on('change', '#wk-multiple-tickets', function () {
                    if ($(this).prop('checked')) {
                        isMultipleTicketsAvailable(1);
                    } else {
                        if ($("body").find("#product_options_container_top").find(".fieldset.new-ticket-type-wrapper:not(.ignore-validate)").length > 1) {
                            // $("body").find("#product_options_container_top").find(".fieldset.new-ticket-type-wrapper:not(:last-child)").remove();
                            var ticketWrapper = $("body").find("#product_options_container_top").find(".fieldset.new-ticket-type-wrapper:not(.ignore-validate)").length;
                            $.each($("body").find("#product_options_container_top").find(".fieldset.new-ticket-type-wrapper:not(.ignore-validate)"), function () {
                                if (ticketWrapper > 1) {
                                    $(this).remove();
                                    ticketWrapper--;
                                }
                            });
                        }

                        isMultipleTicketsAvailable(0);
                    }
                });

                $('body').on('change', '#wk-show-map-loction', function () {
                    if ($(this).prop('checked')) {
                        isShowMapLocation(1);
                    } else {
                        isShowMapLocation(0);
                    }
                });

                $('body').on('change', '#wk-available-event-map', function () {
                    if ($(this).prop('checked')) {
                        isEventChartAvailable(1);
                    } else {
                        isEventChartAvailable(0);
                    }
                });

                function isEventChartAvailable(flag)
                {
                    if (flag) {
                        $('#wk-available-event-map-image').removeClass("required");
                        $('#wk-available-event-map-image').addClass("required");
                        $('.control.event-image-upload').removeClass('wk-bk-hide');
                        $('.control.event-image-upload').show();
                        if ($('body').find('input#wk-available-event-map-hide').length) {
                            $('body').find('input#wk-available-event-map-hide').remove();
                        }
                        // $('#wk-available-event-map').trigger('click');
                    } else {
                        $('.control.event-image-upload').addClass('wk-bk-hide');
                        $('.control.event-image-upload').hide();
                        $('#wk-available-event-map-image').removeClass("required");
                        if ($('body').find('input#wk-available-event-map-hide').length) {
                            $('body').find('input#wk-available-event-map-hide').val(0);
                        } else {
                            $('#wk-available-event-map').after(
                                $("<input>").attr('type', 'hidden').attr('id', 'wk-available-event-map-hide').attr('name', 'product[event_chart_available]').val(0)
                            );
                        }
                    }
                }

                function isShowMapLocation(flag)
                {
                    if (flag) {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').remove();
                        }
                        // $('#wk-show-map-loction').trigger('click');
                    } else {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').val(0);
                        } else {
                            $('#wk-show-map-loction').after(
                                $("<input>").attr('type', 'hidden').attr('id', 'wk-show-map-loction-hide').attr('name', 'product[show_map_loction]').val(0)
                            );
                        }
                    }
                }

                function isMultipleTicketsAvailable(flag)
                {
                    if (flag) {
                        $("#add_new_defined_option").show();
                        if ($('body').find('input#wk-multiple-tickets-hide').length) {
                            $('body').find('input#wk-multiple-tickets-hide').remove();
                        }
                        // $('#wk-multiple-tickets').trigger('click');
                    } else {
                        $("#add_new_defined_option").hide();
                        if ($('body').find('input#wk-multiple-tickets-hide').length) {
                            $('body').find('input#wk-multiple-tickets-hide').val(0);
                        } else {
                            $('#wk-multiple-tickets').after(
                                $("<input>").attr('type', 'hidden').attr('id', 'wk-multiple-tickets-hide').attr('name', 'product[is_multiple_tickets]').val(0)
                            );
                        }
                    }
                }

                function getBookingDate(element)
                {
                    var date;
                    try {
                        date = $.datepicker.parseDate(dateFormat, element.value);
                    } catch (error) {
                        date = null;
                    }
                    return date;
                }
            }
        }
    );
    return $.mpeventbooking.mpeventbooking;
});