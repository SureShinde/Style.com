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
    $.widget('hotelbooking.hotelbooking', {
        options: {},
        _create: function () {
            var self = this;
            var bookedDatesArr = [];
            var availableDatesArr = [];
            var datesArr = [];
            var tempDateRange;

            if (self.options.bookedDates) {
                bookedDatesArr = JSON.parse(self.options.bookedDates);
            }
            if (self.options.availableDates) {
                availableDatesArr = JSON.parse(self.options.availableDates);
            }
            setDefaultDateRange();


            function setDefaultDateRange()
            {
                tempDateRange = $("#date_range").dateRange({
                    'dateFormat': 'd MMM, y',
                    'from': {
                        'id': 'wk-bk-select-date-from',
                    },
                    'to': {
                        'id': 'wk-bk-select-date-to'
                    },
                    'minDate': 'today',
                });
            }

            $("body").on(
                'blur',
                '#wk-bk-select-date-from',
                function (e) {
                    setBlurOnDates();
                }
            );

            $("body").on(
                'blur',
                '#wk-bk-select-date-to',
                function (e) {
                    setBlurOnDates();
                }
            );

            $("body").on(
                "input",
                "#qty.hotel-number-field.no-of-rooms",
                function () {
                    setBlurOnDates();
                }
            );

            function setBlurOnDates()
            {
                var selectedDateFrom = $("#wk-bk-select-date-from").val();
                var selectedDateTo = $("#wk-bk-select-date-to").val();
                if (selectedDateFrom && selectedDateTo) {
                    selectedDateFrom = Date.parse(selectedDateFrom);
                    selectedDateTo = Date.parse(selectedDateTo);
                    var _array = [];
                    for (var key in datesArr) {
                        var booked_dates_str = Date.parse(key);
                        if (booked_dates_str >= selectedDateFrom && booked_dates_str <= selectedDateTo) {
                            _array.push(datesArr[key]);
                        }
                    }
                    if (_array.length > 0) {
                        var maxRoom = Math.min.apply(Math, _array);
                        $("input.input-text.hotel-number-field.no-of-rooms").attr("max", maxRoom);
                        if (maxRoom == 0) {
                            $("input.input-text.hotel-number-field.no-of-rooms").val(0);
                        }
                    } else {
                        var childProduct = $("input[name='selected_configurable_option']").val();
                        if (childProduct && availableDatesArr[childProduct]) {
                            $("input.input-text.hotel-number-field.no-of-rooms").attr("max", availableDatesArr[childProduct]);
                        }
                    }
                } else {
                    var childProduct = $("input[name='selected_configurable_option']").val();
                    if (childProduct && availableDatesArr[childProduct]) {
                        $("input.input-text.hotel-number-field.no-of-rooms").attr("max", availableDatesArr[childProduct]);
                    }
                }
            }

            if (self.options.priceRangeText) {
                $('#product-price-' + self.options.productId).after(
                    '<span class="wk-bk-event-price-range-content" id="wk-bk-price-range-txt">' + self.options.priceRangeText + '</span>'
                );
            }

            $("body").on(
                'click',
                '.hotel-super-attribute-select .room-config-types',
                function () {
                    tempDateRange.dateRange('destroy');
                    setDefaultDateRange();
                    superAttributeSelected($(this));
                }
            );

            function superAttributeSelected(element)
            {
                var superButton = $(element);
                var optionId = $(element).attr("id");

                var superAttributeName = superButton.parents(".hotel-super-attribute-select").data("supername");
                var superAttrPosition = superButton.parents(".hotel-super-attribute-select").data("position");
                if ($("body .hotel-super-attribute-select").length > 1 && superAttrPosition == 1) {
                    $.each(
                        $("body .hotel-super-attribute-select"),
                        function () {
                            if ($(this).data("position") !== 1) {
                                $(this).find(".room-config-types").removeClass("hotel-selected");
                            }
                        }
                    );
                }
                superButton.siblings().removeClass("hotel-selected");
                superButton.removeClass("hotel-selected");
                superButton.addClass("hotel-selected");

                try {
                    $("body").find("select[name='" + superAttributeName + "']").val(optionId);
                    $("body").find("select[name='" + superAttributeName + "']").trigger("change");
                } catch (err) {
}

                var childProduct = $("input[name='selected_configurable_option']").val();
                if (childProduct) {
                    datesArr = [];
                    var disabledDates = [];
                    if (bookedDatesArr) {
                        for (var key in bookedDatesArr) {
                            if (key == childProduct) {
                                for (var date in bookedDatesArr[key]["booked_dates"]) {
                                    datesArr = bookedDatesArr[key]["booked_dates"];
                                    if (bookedDatesArr[key]["booked_dates"][date] <= 0) {
                                        disabledDates.push(date);
                                    }
                                }
                            }
                        }
                    }
                    if (disabledDates.length > 0) {
                        tempDateRange.dateRange('destroy');
                        tempDateRange = $("#date_range").dateRange({
                            'dateFormat': 'd MMM, y',
                            'from': {
                                'id': 'wk-bk-select-date-from',
                            },
                            'to': {
                                'id': 'wk-bk-select-date-to',
                            },
                            'minDate': 'today',
                            "beforeShowDay": function (date) {
                                var string = $.datepicker.formatDate('d M, yy', date);
                                var isDisabled = ($.inArray(string, disabledDates) != -1);
                                return [!isDisabled];
                            }
                        });
                    }

                    setBlurOnDates();
                }
            }

            $("#product_addtocart_form .wk-book-now").unbind('click').on('click', function (e) {
                e.preventDefault();
                if ($("form#product_addtocart_form").valid()) {
                    if (self.options.product_type == "hotelbooking") {
                        var selectedOpt = $("form#product_addtocart_form")
                            .find("input[name='selected_configurable_option']").val();
                        if (selectedOpt == "" || selectedOpt == undefined || selectedOpt == null) {
                            alert({
                                content: self.options.required_config_text
                            });
                        } else {
                            $("form#product_addtocart_form").submit();
                        }
                    } else {
                        $("form#product_addtocart_form").submit();
                    }
                }
            });

            $(".write-answer-container button.write-answer").unbind('click').on("click", function (e) {
                e.preventDefault();

                if (!$(this).parents("form").find('div.answer-field').hasClass('active')) {
                    $(this).parents("form").find('div.answer-field').addClass('active');
                } else {
                    if ($(this).parents("form").valid()) {
                        $(this).parents("form").submit();
                    }
                }
            });

            $("body").on(
                "keypress",
                '.wk-bk-hotel-askquestion-container input#search_question',
                function (e) {
                    if (e.which == 13) {
                        $(this).parents('form').submit();
                    }
                }
            );

            $("body").on(
                "click",
                ".wk-bk-qna-wrapper .more-answers span",
                function () {
                    $(this).parents('.more-answers').siblings('.hidden-answer').show();
                    $(this).parents('.more-answers').hide();
                }
            );

            $("body").on(
                "click",
                ".wk-bk-qna-wrapper .answers.no-display  .more-answers span",
                function () {
                    $(this).parents('.answers.no-display').children('.label').show();
                    $(this).parents('.answers.no-display').find('.write-answer-container').show();
                    $(this).parents('.more-answers').siblings('.hidden-answer').show();
                    $(this).parents('.more-answers').hide();
                    $(this).parents('.answers.no-display').removeClass('no-display');
                }
            );

            $('.hotel-number-quantity .quantity-up').unbind('click').on("click", function () {
                var quantityField = $(this).parents('.hotel-number-quantity');
                var numberFieldinput = quantityField.find('input.hotel-number-field[type="number"]');
                var max = parseInt(numberFieldinput.attr('max'));
                var step = parseInt(numberFieldinput.attr('step'));
                if (!step) {
                    step = 1;
                }

                var numberFieldValue = numberFieldinput.val();
                if (!numberFieldValue) {
                    numberFieldValue = 0;
                }

                var oldValue = parseInt(numberFieldValue);
                if (!max) {
                    var newVal = oldValue + step;
                } else {
                    if (oldValue >= max) {
                        var newVal = oldValue;
                    } else {
                        var newVal = oldValue + step;
                    }
                }

                numberFieldinput.val(newVal);
                numberFieldinput.trigger("change");
            });

            $('.hotel-number-quantity .quantity-down').unbind('click').on("click", function () {
                var quantityField = $(this).parents('.hotel-number-quantity');
                var numberFieldinput = quantityField.find('input.hotel-number-field[type="number"]');
                var min = parseInt(numberFieldinput.attr('min'));
                var step = parseInt(numberFieldinput.attr('step'));
                if (!step) {
                    step = 1;
                }

                var numberFieldValue = numberFieldinput.val();
                if (!numberFieldValue) {
                    numberFieldValue = 1;
                }

                var oldValue = parseInt(numberFieldValue);

                if (oldValue <= min) {
                    var newVal = oldValue;
                } else {
                    var newVal = oldValue - step;
                }

                numberFieldinput.val(newVal);
                numberFieldinput.trigger("change");
            });

        }
    });
    return $.hotelbooking.hotelbooking;
});