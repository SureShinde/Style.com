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
    $.widget('mage.mphotelquestions', {
        options: {},
        _create: function () {
            var self = this;
            $("body").on("change", "select.question_action", function () {
                if ($(this).val()==1) {
                    $(".field.question_status_field").hide();
                } else if ($(this).val()==2) {
                    $(".field.question_status_field").show();
                }
            });
            $('body').delegate('#mass-update-butn','click', function (e) {
                var flag = 0;
                $('.mpcheckbox').each(function () {
                    if (this.checked === true) {
                        flag =1;
                    }
                });
                if (flag === 0) {
                    alert({content : $t(' No Checkbox is checked ')});
                    return false;
                } else {
                    if ($("select.question_action").val()==1) {
                        $(".field.question_status_field").hide();
                        var dicisionapp = confirm($t(" Are you sure you want to delete selected question(s) ? "));
                        if (dicisionapp === true) {
                            $('#form-questionlist-massupdate').submit();
                        } else {
                            return false;
                        }
                    } else {
                        var dicisionapp = confirm($t(" Are you sure you want to update status of selected question(s) ? "));
                        if (dicisionapp === true) {
                            $('#form-questionlist-massupdate').submit();
                        } else {
                            return false;
                        }
                    }
                }
            });
        }
    });
    return $.mage.mphotelquestions;
});
