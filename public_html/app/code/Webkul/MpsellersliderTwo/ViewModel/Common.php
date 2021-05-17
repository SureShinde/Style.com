<?php
/**
 * Webkul_MpsellersliderTwo
 * @category  Webkul
 * @package   Webkul_MpsellersliderTwo
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpsellersliderTwo\ViewModel;

class Common implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public $helper;
    public $mpHelper;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpsellersliderTwo\Helper\Data $helper
    ) {
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
    }

    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    public function getHelper()
    {
        return $this->helper;
    }
}
