<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsellerslider\Plugin\Helper;

class Data
{
    /**
     * function to run to change the return data of GetControllerMappedPermissions.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array                           $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mpsellerslider/mpslider/deleteimage'] = 'mpsellerslider/mpslider/index';
        $result['mpsellerslider/mpslider/index'] = 'mpsellerslider/mpslider/index';
        $result['mpsellerslider/mpslider/savesliderimg'] = 'mpsellerslider/mpslider/index';
        $result['mpsellerslider/mpslider/updateimage'] = 'mpsellerslider/mpslider/index';
        return $result;
    }
}
