define([
    'jquery',
    'Magento_Ui/js/form/element/file-uploader',
    'mage/adminhtml/tools'
], function ($, Uploader) {
    return Uploader.extend({
        defaults: {
            deleteUrl: '',
            textColorField: '',
            modules: {
                colorField: '${ $.textColorField }'
            }
        },

        initObservable: function () {
            return this._super()
                .observe([
                    'codePosX',
                    'codePosY'
                ]);
        },

        onPreviewLoad: function (file, e) {
            this._super(file, e);
            this.initCodeDrag();
            this.colorField().show();
        },

        removeFile: function (file) {
            var deleted = true;

            $.ajax({
                url: this.deleteUrl,
                type: 'GET',
                data: {fileHash: file.name},
                done: function (response) {
                    if (response.error) {
                        deleted = false;
                    }
                }
            });
            if (deleted) {
                this.colorField().hide();
                this._super();
            }

            return this;
        },

        initCodeDrag: function () {
            var img = $("#amgcard_general img")[0];
            var vertical_ratio = img.naturalHeight / img.height;
            var horizontal_ratio = img.naturalWidth / img.width;
            var dragCodeBlock = $('#code_block');

            dragCodeBlock.css('top', parseInt(this.codePosY() / vertical_ratio) + 'px');
            dragCodeBlock.css('left', parseInt(this.codePosX() / vertical_ratio) + 'px');

            $(dragCodeBlock).draggable({
                containment: "#amgcard_general",
                scroll: false,
                stop: function (event, ui) {
                    this.codePosX(ui.position.left * horizontal_ratio);
                    this.codePosY(ui.position.top * vertical_ratio);
                }.bind(this)
            });
        },
    })
});
