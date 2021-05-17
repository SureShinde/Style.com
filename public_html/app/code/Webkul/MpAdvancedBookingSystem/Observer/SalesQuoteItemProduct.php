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
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Quote\CollectionFactory as QuoteCollection;

class SalesQuoteItemProduct implements ObserverInterface
{
     /**
      * @var \Magento\Framework\Message\ManagerInterface
      */
    protected $_messageManager;

     /**
      * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
      */
    protected $helper;

     /**
      * @var \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory
      */
    protected $_quote;

     /**
      * @var QuoteCollection
      */
    protected $_quoteCollection;

    /**
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\MpAdvancedBookingSystem\Model\QuoteFactory $quote,
        QuoteCollection $quoteCollection
    ) {
        $this->_messageManager = $messageManager;
        $this->helper = $helper;
        $this->_quote = $quote;
        $this->_quoteCollection = $quoteCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $helper = $this->helper;
        $actionName = $helper->getFullActionName();
        $notAllowedAction = [
            'checkout_index_index',
            'checkout_sidebar_updateItemQty'
        ];
        if ($item->getBuyRequest() && !in_array($actionName, $notAllowedAction)) {
            $data = $item->getBuyRequest()->getData();
            $productId = $item->getProduct()->getId();
            $itemId = (int) $item->getId();
            if ($this->helper->isBookingProduct($productId)) {
                $collection = $this->_quoteCollection->create();
                $product = $helper->getProduct($productId);
                $productSetId = $product->getAttributeSetId();
                $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
                $appointmentAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Appointment Booking'
                );
                $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Event Booking'
                );
                $rentalAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Rental Booking'
                );
                $hotelAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Hotel Booking'
                );
                $tableAttrSetId = $helper->getProductAttributeSetIdByLabel(
                    'Table Booking'
                );
                if ($actionName == 'sales_order_reorder') {
                    $isThrowError = 1;
                } else {
                    $isThrowError = 0;
                }
                if (in_array($productSetId, $allowedAttrSetIDs) || $productSetId==$hotelAttrSetId) {
                    if ($appointmentAttrSetId == $productSetId) {
                        $helper->processBookingSave(
                            $data,
                            $product,
                            $item,
                            $isThrowError
                        );
                    } elseif ($eventAttrSetId == $productSetId) {
                        $helper->processEventBookingSave(
                            $data,
                            $product,
                            $item,
                            $isThrowError
                        );
                    } elseif ($rentalAttrSetId == $productSetId) {
                        $helper->processRentBookingSave(
                            $data,
                            $product,
                            $item,
                            $isThrowError
                        );
                    } elseif ($productSetId==$hotelAttrSetId) {
                        $helper->processHotelBookingSave(
                            $data,
                            $product,
                            $item,
                            $isThrowError
                        );
                    } elseif ($productSetId==$tableAttrSetId) {
                        $helper->processTableBookingSave(
                            $data,
                            $product,
                            $item,
                            $isThrowError
                        );
                    }
                } elseif (!empty($data['slot_id'])) {
                    $helper->processDefaultSlotData(
                        $data,
                        $item,
                        $productId,
                        $itemId,
                        $isThrowError
                    );
                }
            }
        }
    }
}
