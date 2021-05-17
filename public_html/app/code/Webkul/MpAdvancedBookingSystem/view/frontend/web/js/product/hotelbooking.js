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
        'mphotelbooking.mphotelbooking',
        {
            options: {},
            _create: function () {
                var self = this;
                var showMapLocation = parseInt(self.options.showMapLocation);
                var showNearbyMap = parseInt(self.options.showNearbyMap);
                var askQuesEnable = parseInt(self.options.askQuesEnable);

                isShowMapLocation(showMapLocation);
                isShowNearbyMap(showNearbyMap);
                isAskQuesEnable(askQuesEnable);

                $(".wk-booking-slot-picker").timepicker({
                    timeFormat: 'hh:mm tt',
                    controlType: 'select',
                    'showButtonPanel': true,
                    'showOn': "button",
                    'buttonImage': null,
                    'buttonImageOnly': null,
                    'buttonText': ''
                });

                $('body').on('change', '#wk-show-map-loction', function () {
                    if ($(this).prop('checked')) {
                        isShowMapLocation(1);
                    } else {
                        isShowMapLocation(0);
                    }
                });

                $('body').on('change', '#wk-show-nearby-map', function () {
                    if ($(this).prop('checked')) {
                        isShowNearbyMap(1);
                    } else {
                        isShowNearbyMap(0);
                    }
                });

                $('body').on('change', '#wk-ask-ques-enable', function () {
                    if ($(this).prop('checked')) {
                        isAskQuesEnable(1);
                    } else {
                        isAskQuesEnable(0);
                    }
                });

                function isShowMapLocation(flag)
                {
                    if (flag) {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').remove();
                        }
                    } else {
                        if ($('body').find('input#wk-show-map-loction-hide').length) {
                            $('body').find('input#wk-show-map-loction-hide').val(0);
                        } else {
                            $('#wk-show-map-loction').after(
                                $("<input>").attr('type','hidden').attr('id','wk-show-map-loction-hide').attr('name','product[show_map_loction]').val(0)
                            );
                        }
                    }
                }

                function isShowNearbyMap(flag)
                {
                    if (flag) {
                        if ($('body').find('input#wk-show-nearby-map-hide').length) {
                            $('body').find('input#wk-show-nearby-map-hide').remove();
                        }
                    } else {
                        if ($('body').find('input#wk-show-nearby-map-hide').length) {
                            $('body').find('input#wk-show-nearby-map-hide').val(0);
                        } else {
                            $('#wk-show-nearby-map').after(
                                $("<input>").attr('type','hidden').attr('id','wk-show-nearby-map-hide').attr('name','product[show_nearby_map]').val(0)
                            );
                        }
                    }
                }

                function isAskQuesEnable(flag)
                {
                    if (flag) {
                        if ($('body').find('input#wk-ask-ques-enable-hide').length) {
                            $('body').find('input#wk-ask-ques-enable-hide').remove();
                        }
                    } else {
                        if ($('body').find('input#wk-ask-ques-enable-hide').length) {
                            $('body').find('input#wk-ask-ques-enable-hide').val(0);
                        } else {
                            $('#wk-ask-ques-enable').after(
                                $("<input>").attr('type','hidden').attr('id','wk-ask-ques-enable-hide').attr('name','product[ask_a_ques_enable]').val(0)
                            );
                        }
                    }
                }
            }
        }
    );
    return $.mphotelbooking.mphotelbooking;
});