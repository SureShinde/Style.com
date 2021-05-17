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
namespace Webkul\MpAdvancedBookingSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Booked\CollectionFactory;

class AfterCancelOrder implements ObserverInterface
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Order
     */
    protected $_bookingHelper;

    /**
     * @var CollectionFactory
     */
    protected $bookedCollection;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Order $bookingHelper
     * @param CollectionFactory                          $bookedCollection
     */
    public function __construct(
        \Webkul\MpAdvancedBookingSystem\Helper\Order $bookingHelper,
        CollectionFactory $bookedCollection
    ) {
        $this->_bookingHelper = $bookingHelper;
        $this->bookedCollection = $bookedCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            foreach ($order->getAllItems() as $item) {
                $this->updateBookedSlotsInfo($item, $order);
            }
            $this->_bookingHelper->clearCache();
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterCancelOrder execute : ".$e->getMessage()
            );
        }
    }

    /**
     * Set Booking Slots Info
     *
     * @param object $item
     * @param object $order
     */
    public function updateBookedSlotsInfo($item, $order)
    {
        try {
            $helper = $this->_bookingHelper;
            $orderId = $order->getId();
            $customerId = (int) $order->getCustomerId();
            $quoteItemId = $item->getQuoteItemId();
            $bookingData = $helper->getDetailsByQuoteItemId($quoteItemId);
            $itemId = $item->getId();
            $productId = $item->getProductId();
            $cancelQty = $item->getQtyCanceled();
            if (!$bookingData['error']) {
                $slotId = $bookingData['slot_id'];
                $parentId = $bookingData['parent_slot_id'];
                $collection = $this->bookedCollection->create()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('order_item_id', $itemId)
                    ->addFieldToFilter('item_id', $bookingData['item_id'])
                    ->addFieldToFilter('product_id', $productId)
                    ->addFieldToFilter('slot_id', $slotId)
                    ->addFieldToFilter('parent_slot_id', $parentId)
                    ->addFieldToFilter('customer_id', $customerId);
                if ($collection->getSize()) {
                    foreach ($collection as $data) {
                        if ($cancelQty==$data->getQty()) {
                            $data->delete();
                        } else {
                            $data->setQty($data->getQty()-$cancelQty)->save();
                        }
                    }
                }
                $bookingInfo = $helper->getBookingInfo($productId);
                if ($bookingInfo['is_booking']) {
                    $slotData = $helper->getSlotData(
                        $slotId,
                        $parentId,
                        $productId,
                        $bookingData
                    );
                    $attributeSetId = 0;
                    if (!empty($slotData['attribute_set_id'])) {
                        $attributeSetId = $slotData['attribute_set_id'];
                    }
                    $bookingData['qty'] = $cancelQty;
                    $bookingData['product_id'] = $productId;
                    $bookingData['attribute_set_id'] = $attributeSetId;
                    $helper->saveBookingInfoData(
                        $bookingData,
                        $bookingInfo
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger(
                "Observer_AfterCancelOrder updateBookedSlotsInfo : ".$e->getMessage()
            );
        }
    }
}
