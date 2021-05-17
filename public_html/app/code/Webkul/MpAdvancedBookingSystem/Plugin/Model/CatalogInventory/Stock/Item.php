<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Plugin\Model\CatalogInventory\Stock;

class Item
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(\Webkul\MpAdvancedBookingSystem\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * afterGetQty
     *
     * @param \Magento\CatalogInventory\Model\Stock\Item $subject
     * @param int $result
     * @return int
     */
    public function afterGetQty(\Magento\CatalogInventory\Model\Stock\Item $subject, $result)
    {
        try {
            $productId = $subject->getProductId();
            $helper = $this->helper;
            if ($helper->isBookingProduct($productId, true)) {
                return $helper->getTotalBookingQty($productId);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Model_CatalogInventory_Stock_Item_afterGetQty Exception : ".$e->getMessage()
            );
        }
        return $result;
    }
}
