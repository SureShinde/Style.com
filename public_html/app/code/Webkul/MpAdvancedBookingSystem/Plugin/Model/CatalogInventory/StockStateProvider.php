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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Model\CatalogInventory;

use Magento\CatalogInventory\Api\Data\StockItemInterface;

class StockStateProvider
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * afterCheckQty
     *
     * @param \Magento\CatalogInventory\Model\StockStateProvider $subject
     * @param boolean $result
     * @param StockItemInterface $stockItem
     * @return boolean
     */
    public function afterCheckQty(
        \Magento\CatalogInventory\Model\StockStateProvider $subject,
        $result,
        StockItemInterface $stockItem
    ) {
        try {
            if ($result == false) {
                $helper = $this->helper;
                $product = $helper->getProduct($stockItem->getProductId());
                $productSetId = $product->getAttributeSetId();
                $hotelAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Hotel Booking'
                );
                if (($product->getTypeId() == "virtual" || $product->getTypeId()=="hotelbooking")
                    && $productSetId==$hotelAttrSetId
                ) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Model_CatalogInventory_StockStateProvider_afterCheckQty Exception : ".$e->getMessage()
            );
        }
        return $result;
    }
}
