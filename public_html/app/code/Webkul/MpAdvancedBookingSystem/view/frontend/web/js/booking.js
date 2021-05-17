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
    "jquery"
], function ($) {
    'use strict';

    $.widget('mpbooking.mpbooking', {
        options: {},
        _create: function () {
            var self = this;
            manageWeightField();

            function manageWeightField()
            {
                if ($("body").find("#weight").length) {
                    $('#weight').parents('.field').remove()
                }
            }

            if ($("body").find("div[data-block='product-custom-options']").length) {
                $("body").find("div[data-block='product-custom-options']").remove();
            }

            if ($("body").find("#special-price").length) {
                $('#special-price').parent().parent().remove();
            }
            if ($("body").find("#special-from-date").length) {
                $('#special-from-date').parent().parent().remove();
            }
            if ($("body").find("#special-to-date").length) {
                $('#special-to-date').parent().parent().remove();
            }
        }
    });
    return $.mpbooking.mpbooking;
});