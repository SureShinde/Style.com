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
namespace Webkul\MpsellersliderTwo\Plugin\Helper;

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
        $result['mpsellerslidertwo/mpslidertwo/deleteimage'] = 'mpsellerslidertwo/mpslidertwo/index';
        $result['mpsellerslidertwo/mpslidertwo/index'] = 'mpsellerslidertwo/mpslidertwo/index';
        $result['mpsellerslidertwo/mpslidertwo/savesliderimg'] = 'mpsellerslidertwo/mpslidertwo/index';
        $result['mpsellerslidertwo/mpslidertwo/updateimage'] = 'mpsellerslidertwo/mpslidertwo/index';
        return $result;
    }
}
