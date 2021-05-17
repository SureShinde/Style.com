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
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
], function ($, $t, alert) {
    'use strict';
    $.widget('mage.mphotelanswers', {
        options: {},
        _create: function () {
            var self = this;
            $('body').delegate('#mass-delete-button','click', function (e) {
                var flag =0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    var dicisionapp=confirm($t(" Are you sure you want to delete selected answer(s) ? "));
                    if (dicisionapp === true) {
                        $('#form-answerlist-massupdate').submit();
                    } else {
                        return false;
                    }
                }
            });
        }
    });
    return $.mage.mphotelanswers;
});
