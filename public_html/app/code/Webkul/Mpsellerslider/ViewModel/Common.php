<?php
/**
 * Webkul_Mpsellerslider
 * @category  Webkul
 * @package   Webkul_Mpsellerslider
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpsellerslider\ViewModel;

class Common implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public $helper;
    public $mpHelper;

    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpsellerslider\Helper\Data $helper
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
