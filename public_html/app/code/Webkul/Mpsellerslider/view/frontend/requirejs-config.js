/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    map: {
        '*': {
            mpSellerSlider: 'Webkul_Mpsellerslider/js/seller-slider',
            sellerProfileJsSlider : 'Webkul_Mpsellerslider/js/slider-js-1.3',
            manageSlider : 'Webkul_Mpsellerslider/js/manage-slider',
            mpColorPickerFunction: 'Webkul_Marketplace/js/color-picker-function',
        }
    },
    paths: {
        "sellerProfileJsSlider": 'Webkul_Mpsellerslider/js/slider-js-1.3'
    },
    "shim": {
        "sellerProfileJsSlider" : ["jquery"]
    }
};
