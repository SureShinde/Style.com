/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define(
    [
    "jquery",
    'mage/translate',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'jquery/validate',
    "jquery/ui"
    ],
    function ($, $t,mageTemplate, alert, modal) {
        'use strict';

        $.widget(
            'mage.mpSellerSlider',
            {
                options: {
                    formId: '#form-slider-validate',
                    imageSet: '.image_set img',
                    imageSetSpan: '.image_set span',
                    imageSetDeleteSpan: '.image_set span.wk_imagedelete',
                    imageSetEditSpan: '.image_set span.wk_imageedit',
                    cnt:'#cnt',
                    filecount:'#filecount',
                    otherimages:'#otherimages',
                    addimgs:'.addimgs',
                    remov:'.remov',
                    loader:'.wk_mpslider_loader',
                },
                _create: function () {
                    var self = this;

                    $(self.options.imageSet).mouseover(
                        function (event) {
                            $(event.target).parent('a').find('span').css({display:'block'});
                            $(event.target).parent('a').find('img').css('opacity','0.4');
                        }
                    );
                    $(self.options.imageSet).mouseout(
                        function (event) {
                            $(event.target).parent('a').find('span').css({display:'none'});
                            $(event.target).parent('a').find('img').css('opacity','1');
                        }
                    );

                    $(self.options.imageSetSpan).mouseover(
                        function (event) {
                            $(event.target).parent('a').find('span').css({display:'block'});
                            $(event.target).parent('a').find('img').css('opacity','0.4');
                        }
                    );
                    $(self.options.imageSetSpan).mouseout(
                        function (event) {
                            $(event.target).parent('a').find('span').css({display:'none'});
                            $(event.target).parent('a').find('img').css('opacity','1');
                        }
                    );
                    $("#save_butn").click(function(e){
                        e.preventDefault();
                    var imageUrl = $('[name ="sliderimgurl_0"]').val();
                    if(imageUrl){
                        $('[name ="sliderimg_0"]').attr('required','required');
                    }else{
                        $('[name ="sliderimg_0"]').removeAttr('required');  
                    }
                });

                    /*-----------event to add more images------------*/
                    $(self.options.addimgs).on(
                        'click',
                        function () {

                            var i=$(self.options.cnt).val();
                            var progressTmpl = mageTemplate('#addimages-template'),tmpl;
                            tmpl = progressTmpl(
                                {
                                    data: {
                                        i: i,
                                    }
                                }
                            );
                            $(self.options.otherimages).append(tmpl);
                            $(self.options.cnt).val(++i);
                        }
                    );

                    $('body').on(
                        'click',
                        self.options.remov,
                        function () {

                            var ids = $(this).attr('id');
                            $(this).parent().remove();

                            var count=$(self.options.cnt).val();
                            if (count>0) {
                                $(self.options.cnt).val(--count);
                            }
                        }
                    );

                    $(self.options.imageSetDeleteSpan).click(
                        function (event) {
                            event.preventDefault();
                            self.callAjaxFunction($(this));
                        }
                    );
                    $(self.options.imageSetEditSpan).click(
                        function (event) {
                            event.preventDefault();
                            var dicisionapp = confirm('Sure Want To Edit Image Url');
                            if (dicisionapp==true) {
                                // var popup = modal(options, $('#edit-popup-modal'));
                                // $("#edit-popup-modal").modal("openModal");
                                var elemntUpdate = $(this).parent('a');
                                var elemntSavedUrl = $(this).parent('a').attr('href');
                                var imag = $(this).parent('a').find('img').attr('id');
                                var imagsrc = $(this).parent('a').find('img').attr('src');
                                var popup = $('<div class="edit-image-url-popup"/>')
                                    .html('<img src="'+imagsrc+'"><input type="text" class="validate-url edit_image_url" name="editimgurl" id="'+imag+'" value="'+elemntSavedUrl+'" placeholder="'+$.mage.__("Add Link Url")+'" />').modal(
                                        {
                                            modalClass: 'changelog',
                                            title: $.mage.__("Edit Url"),
                                            buttons: [{
                                                text: $.mage.__('Update'),
                                                click: function () {
                                                    var newImageUrl = $(this.element).find('.edit_image_url').val();

                                                    var tempval = $.validator.validateSingleElement($(this.element).find('.edit_image_url'));
                                                    if (tempval) {
                                                        self.callAjaxFunctionForEdit(newImageUrl, imag, elemntUpdate, this);
                                                    }
                                                }
                                            }]
                                        }
                                    );
                                popup.modal('openModal');
                            }
                        }
                    );

                    $('#save_butn').click(
                        function (e) {
                            if ($(self.options.formId).valid()!==false) {
                                var i = [];
                                $(self.options.formId+" .sliderimg").each(
                                    function () {
                                        if ($(this).val()!=="" && i.indexOf($(this).data('counter')) == -1) {
                                            i.push($(this).data('counter'));
                                        }
                                    }
                                );
                                if (i.length) {
                                    $(self.options.filecount).val(i.join());
                                }
                                $(self.options.formId).submit();
                            }
                        }
                    )
                },
                /*---ajax to delete an image of slider-------*/
                callAjaxFunction: function (imgSpan) {
                    var self = this;
                    var dicisionapp = confirm('Are you sure you want to delete this image ?');
                    if (dicisionapp==true) {
                        var imag = imgSpan.prev('img').attr('id');
                        var loaderHtml = $(self.options.loader).get(0);
                        $(self.options.loader).remove();
                        $('body').prepend(loaderHtml);
                        $('body '+self.options.loader).show();

                        $.ajax({
                            type: "POST",
                            url: self.options.ajaxSaveUrl,
                            data: {
                                file:imag
                            },
                            success: function (response) {
                                var alertPopup = alert({
                                    content: $t(response),
                                });
                                if ($(alertPopup)[0]) {
                                    var ele = $(alertPopup)[0];
                                    setTimeout(function () {
                                        $(ele.parentElement).siblings(".modal-footer").find("button").trigger("click")
                                    }, 2000);
                                }
                                
                                imgSpan.parents('div.setimage').remove();
                                $('body '+self.options.loader).hide();
                            },
                            error: function (response) {
                                alert({
                                    content: $t('There was error in deleting image')
                                });
                            }
                        });
                    }
                },
                /*---ajax to edit an image url of slider-------*/
                callAjaxFunctionForEdit: function (imgEditUrl, imgEditId, elemntUpdate, popupeditmodal) {
                    var self = this;
                    var loaderHtml = $(self.options.loader).get(0);
                    $(self.options.loader).remove();
                    $('body').prepend(loaderHtml);
                    $('body '+self.options.loader).show();
                    $.ajax(
                        {
                            type: "POST",
                            url: self.options.ajaxUpdateUrl,
                            data: {
                                file:imgEditId,
                                img_url: imgEditUrl
                            },
                            success: function (response) {
                                alert(
                                    {
                                        content: $t(response)
                                    }
                                );
                                $('body '+self.options.loader).hide();
                            },
                            error: function (response) {
                                alert(
                                    {
                                        content: $t('There was error in updating image URL')
                                    }
                                );
                            }
                        }
                    ).done(
                        function () {
                                $(elemntUpdate).attr('href', imgEditUrl);
                                popupeditmodal.closeModal();
                        }
                    );
                }
            }
        );
        return $.mage.mpSellerSlider;
    }
);
