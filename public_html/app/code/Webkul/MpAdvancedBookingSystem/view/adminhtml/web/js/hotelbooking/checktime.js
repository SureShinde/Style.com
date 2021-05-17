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
    "mage/calendar"
], function ($) {
    'use strict';
    $.widget(
        'hotelchecktime.hotelchecktime',
        {
            options: {
            },
            _create: function () {
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
    );
    return $.hotelchecktime.hotelchecktime;
});
