<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpStripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\ViewModel;

class MarketplaceHelper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Helper\Orders $mpOrderHelper
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Helper\Orders $mpOrderHelper
    ) {
        $this->mpHelper = $mpHelper;
        $this->mpOrderHelper = $mpOrderHelper;
    }

    /**
     * get marketplace helper
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * get marketplace order helper
     *
     * @return \Webkul\Marketplace\Helper\Orders
     */
    public function getMpOrderHelper()
    {
        return $this->mpOrderHelper;
    }
}
